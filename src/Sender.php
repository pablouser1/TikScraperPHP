<?php
namespace TikScraper;

use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use TikScraper\Constants\Responses;
use TikScraper\Helpers\Algorithm;
use TikScraper\Helpers\Misc;
use TikScraper\Helpers\Request;
use TikScraper\Models\Response;

class Sender {
    private HTTPClient $httpClient;
    private Signer $signer;
    private bool $testEndpoints = false;
    private string $userAgent;

    private const DEFAULT_API_HEADERS = [
        "authority" => "m.tiktok.com",
        "method" => "GET",
        "scheme" => "https",
        "accept" => "application/json, text/plain, */*",
        "accept-encoding" => "gzip, deflate, br",
        "accept-language" => "en-US,en;q=0.9",
        "sec-fetch-dest" => "empty",
        "sec-fetch-mode" => "cors",
        "sec-fetch-site" => "same-site",
        "sec-gpc" => "1"
    ];

    function __construct(array $config) {
        // Signing
        if (!isset($config['signer'])) {
            throw new \Exception("You need to set a signer config! Please check the README for more info");
        }
        if (isset($config['use_test_endpoints']) && $config['use_test_endpoints']) $this->testEndpoints = true;

        $this->httpClient = new HTTPClient($config);
        $this->signer = new Signer($config['signer']);
    }

    /**
     * Send request to TikTok's internal API
     * @param string $endpoint
     * @param string $subdomain Subdomain to be used, may be m, t or www
     * @param array $query Custom query to be sent, later to me merged with some default values
     * @param ?SetCookie $ttwid Send or not ttwid cookie, only used for trending
     */
    public function sendApi(
        string $endpoint,
        string $subdomain = 'm',
        array $query = [],
        ?SetCookie $ttwid = null
    ): Response {
        $client = $this->httpClient->getClient();
        $useragent = $this->httpClient->getUserAgent();
        $jar = $this->httpClient->getJar();
        $msToken = '';

        // Use test subdomain if test endpoints are enabled
        if ($this->testEndpoints && $subdomain === 'm') {
            $subdomain = 't';
        }

        // Get msToken used for signing
        $msTokenCookie = $jar->getCookieByName("msToken");
        if ($msTokenCookie !== null) {
            $msToken = $msTokenCookie->getValue();
        }

        $headers = [];
        $url = 'https://' . $subdomain . '.tiktok.com' . $endpoint;
        $device_id = Algorithm::deviceId();
        $headers[] = "Path: $endpoint";
        $url .= Request::buildQuery($query, $msToken) . '&device_id=' . $device_id;
        $headers = array_merge($headers, self::DEFAULT_API_HEADERS);
        // URL to send to signer
        $signer_res = $this->signer->run($url);
        if ($signer_res && $signer_res->status === 'ok') {
            $url = $signer_res->data->signed_url;
            $useragent = $signer_res->data->navigator->user_agent;
            if ($ttwid !== null) {
                // Add ttwid to ram-only CookieJar for request
                $jar = new CookieJar(false, $jar->toArray());
                $jar->setCookie($ttwid);
            }
        } else {
            // Signing error
            return Responses::badSign();
        }

        $httpRes = null;

        try {
            $res = $client->get($url, [
                'jar' => $jar,
                'headers' => [
                    $headers,
                    ...['User-Agent' => $useragent]
                ]
            ]);
            $httpRes = $res;
        } catch (RequestException $e) {
            // The server responded a bad code (403, 500...)
            $httpRes = $e->getResponse();
        } catch (ConnectException $e) {
            // The server does not respond
            $httpRes = null;
        }

        return new Response($httpRes);
    }

    /**
     * Send request to TikTok website
     * @param string $endpoint
     * @param string $subdomain Subdomain to be used, may be m or www
     * @param string $query Query to append to URL
     */
    public function sendHTML(
        string $endpoint,
        string $subdomain = 'www',
        array $query = [],
        bool $solvedChallenge = false
    ): Response {
        $client = $this->httpClient->getClient();

        $url = 'https://' . $subdomain . '.tiktok.com' . $endpoint;
        // Add query
        if (!empty($query)) $url .= '?' . http_build_query($query);
        $httpRes = null;

        try {
            $res = $client->get($url);
            $httpRes = $res;
        } catch (RequestException $e) {
            // The server responded a bad code (403, 500...)
            $httpRes = $e->getResponse();
        } catch (ConnectException $e) {
            // The server does not respond
            $httpRes = null;
        }

        $res = new Response($httpRes);
        if ($res->isChallenge) {
            // Drop if got another challenge after properly solving one
            if ($solvedChallenge) {
                return Responses::badChallenge();
            }

            // Make challenge and resend
            $solved = $this->__solveInitialChallenge($res);
            return $solved ? $this->sendHTML($endpoint, $subdomain, $query, true) : $res;
        }

        return $res;
    }

    /**
     * Sends a GET/HEAD request to TikTok, usually used to get some required cookies/headers for later
     * @param $url URL to be used
     * @return array 'cookies' and 'headers'
     */
    public function sendHead(string $url, CookieJar $jar): array {
        $client = $this->httpClient->getClient();

        $res = $client->head($url, [
            'cookies' => $jar
        ]);

        return $res->getHeaders();
    }

    /**
     * Solves TikTok's own challenge to avoid bots with a JS crypto solve
     */
    private function __solveInitialChallenge(Response $res): bool {
        if ($res->http_success && $res->isHtml) {
            $dom = Misc::getDoc($res->origRes->getBody());
            if ($dom !== null) {
                $type = $dom->getElementById("wci");
                $key = $dom->getElementById("cs");
                if ($type !== null && $key !== null) {
                    $typeName = $type->getAttribute('class');
                    $cookieValue = Algorithm::challenge($typeName, $key->getAttribute('class'));

                    $cookie = new SetCookie;
                    $cookie->setName($typeName);
                    $cookie->setValue($cookieValue);
                    $cookie->setDomain("www.tiktok.com");
                    $cookie->setPath("/");
                    $cookie->setMaxAge(1);
                    $this->httpClient->getJar()->setCookie($cookie);
                    return true;
                }
            }
        }

        return false;
    }
}
