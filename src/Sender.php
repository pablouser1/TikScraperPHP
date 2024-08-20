<?php
namespace TikScraper;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use TikScraper\Helpers\Request;
use TikScraper\Models\Response;

class Sender {
    private const WEB_URL = "https://www.tiktok.com";
    private const API_URL = self::WEB_URL . "/api";
    private Selenium $selenium;
    private Guzzle $guzzle;

    function __construct(array $config) {
        $this->selenium = new Selenium($config);
        $this->guzzle = new Guzzle($config);
    }

    /**
     * Send request to TikTok's internal API
     * @param string $endpoint Api endpoint
     * @param array $query Custom query to be sent, later to me merged with some default values
     */
    public function sendApi(
        string $endpoint,
        array $query = [],
        string $referrer = "/foryou"
    ): Response {
        $driver = $this->selenium->getDriver();
        $nav = $this->selenium->getNavigator();
        $full_referrer = self::WEB_URL . $referrer;
        $url = self::API_URL . $endpoint . Request::buildQuery($query, $nav, $this->selenium->getVerifyFp());

        $res = $driver->executeAsyncScript(
            "var callback = arguments[2]; window.fetchApi(arguments[0], arguments[1]).then(d => callback(d))",
            [$url, $full_referrer]
        );

        return new Response($res);
    }

    /**
     * Send regular HTML request using Guzzle
     * @param string $endpoint HTML endpoint
     * @param string $subdomain Subdomain to be used
     */
    public function sendHTML(string $endpoint, string $subdomain): Response {
        $client = $this->guzzle->getClient();
        $url = "https://" . $subdomain . ".tiktok.com" . $endpoint;

        $data = [
            "type" => "html",
            "code" => -1,
            "success" => false,
            "data" => null
        ];

        try {
            $res = $client->get($url);
            $code = $res->getStatusCode();
            $data["code"] = $code;
            $data["success"] = $code >= 200 && $code < 400;
            $data["data"] = (string) $res->getBody();
        } catch (ClientException | ServerException $e) {
            $code = $e->getCode();
            $data["code"] = $code;
            $data["success"] = $code >= 200 && $code < 400;
        } catch (ConnectException $e) {
            $data["code"] = 503;
        }

        return new Response($data);
    }
}
