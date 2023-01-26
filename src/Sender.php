<?php
namespace TikScraper;

use TikScraper\Constants\UserAgents;
use TikScraper\Helpers\Algorithm;
use TikScraper\Helpers\Request;
use TikScraper\Models\Response;

class Sender {
    private const REFERER = 'https://www.tiktok.com/';

    private const DEFAULT_API_HEADERS = [
        "authority: m.tiktok.com",
        "method: GET",
        "scheme: https",
        "accept: application/json, text/plain, */*",
        "accept-encoding: gzip, deflate, br",
        "accept-language: en-US,en;q=0.9",
        "sec-fetch-dest: empty",
        "sec-fetch-mode: cors",
        "sec-fetch-site: same-site",
        "sec-gpc: 1"
    ];

    private Signer $signer;
    private array $proxy = [];
    private bool $use_test_endpoints = false;
    private string $useragent = UserAgents::DEFAULT;
    private string $cookie_file = '';

    function __construct(array $config) {
        // Signing
        if (!isset($config['signer'])) {
            throw new \Exception("You need to send a signer config! Please check the README for more info");
        }

        $signer_config = $config['signer'];

        $this->signer = new Signer($signer_config);

        $this->proxy = $config['proxy'] ?? [];
        if (isset($config['use_test_endpoints']) && $config['use_test_endpoints']) $this->use_test_endpoints = true;
        $this->useragent = $config['user_agent'] ?? UserAgents::DEFAULT;
        $this->cookie_file = sys_get_temp_dir() . '/tiktok.txt';
    }

    /**
     * Send request to TikTok's internal API
     * @param string $endpoint
     * @param string $subdomain Subdomain to be used, may be m, t or www
     * @param array $query Custom query to be sent, later to me merged with some default values
     * @param bool $send_tt_params Send or not x-tt-params header, some endpoints use it
     * @param string $ttwid Send or not ttwid cookie, only used for trending
     * @param string $static_url URL to be used instead of $endpoint to bypass some captchas
     */
    public function sendApi(
        string $endpoint,
        string $subdomain = 'm',
        array $query = [],
        bool $send_tt_params = false,
        string $ttwid = '',
        string $static_url = ''
    ): Response {
        // Use test subdomain if test endpoints are enabled
        if ($this->use_test_endpoints && $subdomain === 'm') {
            $subdomain = 't';
        }
        $headers = [];
        $cookies = '';
        $ch = curl_init();
        $url = 'https://' . $subdomain . '.tiktok.com' . $endpoint;
        $useragent = $this->useragent;
        $device_id = Algorithm::deviceId();

        $headers[] = "Path: $endpoint";
        $url .= Request::buildQuery($query) . '&device_id=' . $device_id;
        $headers = array_merge($headers, self::DEFAULT_API_HEADERS);
        // URL to send to signer
        $signer_res = $this->signer->run($url);
        if ($signer_res && $signer_res->status === 'ok') {
            $url = $signer_res->data->signed_url;
            $useragent = $signer_res->data->navigator->user_agent;
            if ($send_tt_params) {
                $headers[] = 'x-tt-params: ' . $signer_res->data->{'x-tt-params'};
            }
            if ($ttwid) {
                $cookies .= 'ttwid=' . $ttwid . ';';
            }
        } else {
            // Signing error
            return new Response(false, 500, (object) [
                'statusCode' => 20
            ]);
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $static_url ? $static_url : $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => $useragent,
            CURLOPT_ENCODING => 'utf-8',
            CURLOPT_AUTOREFERER => true,
            CURLOPT_REFERER => self::REFERER,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
        ]);
        if ($cookies) {
            curl_setopt($ch, CURLOPT_COOKIE, $cookies);
        }

        Request::handleProxy($ch, $this->proxy);
        $data = curl_exec($ch);
        $error = curl_errno($ch);
        $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        if ($data && !$error) {
            // Request sent
            return new Response($code >= 200 && $code < 400, $code, json_decode($data));
        }

        // Return an error if the request didn't happen (timeouts for example)
        return new Response(false, 503, (object) [
            'statusCode' => 10
        ]);
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
        array $query = []
    ): Response {
        $ch = curl_init();
        $url = 'https://' . $subdomain . '.tiktok.com' . $endpoint;
        $useragent = $this->useragent;
        // Add query
        if (!empty($query)) $url .= '?' . http_build_query($query);
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => $useragent,
            CURLOPT_ENCODING => 'utf-8',
            CURLOPT_AUTOREFERER => true,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_COOKIEJAR => $this->cookie_file,
            CURLOPT_COOKIEFILE => $this->cookie_file,
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
        ]);
        Request::handleProxy($ch, $this->proxy);
        $data = curl_exec($ch);
        $error = curl_errno($ch);
        $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        if (!$error) {
            // Request sent
            return new Response($code >= 200 && $code < 400, $code, $data);
        }
        return new Response(false, 503, '');
    }

    /**
     * Sends a GET/HEAD request to TikTok, usually used to get some required cookies/headers for later
     * @param $url URL to be used
     * @param $headMethod Send a HEAD request if true or a GET request if false
     * @param $reqHeaders Optional aditional headers to send
     * @return array 'cookies' and 'headers'
     */
    public function sendHead(string $url, bool $headMethod = false, array $reqHeaders = []): array {
        $headers = [];
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_NOBODY => $headMethod,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => $this->useragent,
            CURLOPT_ENCODING => 'utf-8',
            CURLOPT_AUTOREFERER => true,
            CURLOPT_HTTPHEADER => $reqHeaders
        ]);

        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($curl, $header) use (&$headers) {
            $len = strlen($header);
            $header = explode(':', $header, 2);
            if (count($header) < 2) return $len;
            $headers[strtolower(trim($header[0]))][] = trim($header[1]);
            return $len;
        });

        Request::handleProxy($ch, $this->proxy);

        $data = curl_exec($ch);
        curl_close($ch);
        return [
            'cookies' => Request::extractCookies($data),
            'headers' => $headers
        ];
    }
}
