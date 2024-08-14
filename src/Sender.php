<?php
namespace TikScraper;
use Facebook\WebDriver\Chrome\ChromeDevToolsDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Cookie;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverWait;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use SapiStudio\SeleniumStealth\SeleniumStealth;
use TikScraper\Helpers\Request;
use TikScraper\Models\Response;

class Sender {
    private const WEB_URL = "https://www.tiktok.com";
    private const API_URL = "https://www.tiktok.com/api";
    const DEFAULT_HEADERS = [
        "Accept" => "*/*",
        "Accept-Language" => "en-US,en;q=0.5",
        "Accept-Encoding" => "gzip, deflate, br",
        "Referer" => "https://www.tiktok.com/foryou"
    ];

    private string $verifyFp = "";
    private RemoteWebDriver $driver;
    private HTTPClient $httpClient;

    function __construct(array $config) {
        $this->_setupSelenium($config);

        $this->httpClient = new HTTPClient($config);
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
        $nav = $this->_navigator();
        $full_referrer = self::WEB_URL . $referrer;
        $url = self::API_URL . $endpoint . Request::buildQuery($query, $nav, $this->verifyFp);

        $res = $this->driver->executeAsyncScript(
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
        $client = $this->httpClient->getClient();
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

    private function _navigator(): object {
        return (object) $this->driver->executeScript("return {
            user_agent: window.navigator.userAgent,
            browser_language: window.navigator.language,
            browser_platform: window.navigator.platform,
            browser_name: window.navigator.appCodeName,
            browser_version: window.navigator.appVersion
        }");
    }

    private function _setupSelenium(array $config): void {
        $this->verifyFp = $config["verify_fp"] ?? "";
        $debug = isset($config["debug"]) ? boolval($config["debug"]) : false;
        $url = $config["chromedriver"] ?? "http://localhost:4444";

        // Chrome flags
        $opts = new ChromeOptions();
        if (!$debug) {
            // Enable headless if not debugging
            $opts->addArguments(["--headless"]);
        }

        $cap = DesiredCapabilities::chrome();
        $cap->setCapability(ChromeOptions::CAPABILITY_W3C, $opts);

        // TODO: Get session id using other method instead of using deprecated function
        $sessions = RemoteWebDriver::getAllSessions($url);

        if (count($sessions) > 0) {
            // Reuse session
            $this->driver = RemoteWebDriver::createBySessionID($sessions[0]["id"], $url, null, null, true, $cap);
        } else {
            $this->_buildSeleniumSession($url);
        }
    }

    private function _buildSeleniumSession(string $url) {
        $js = file_get_contents(__DIR__ . "/../js/fetch.js");
        // Create session
        $tmpDriver = RemoteWebDriver::create($url, DesiredCapabilities::chrome());
        $this->driver = (new SeleniumStealth($tmpDriver))->usePhpWebriverClient()->makeStealth();

        // Inject custom JS code for fetching TikTok's API
        $devTools = new ChromeDevToolsDriver($this->driver);
        $devTools->execute("Page.addScriptToEvaluateOnNewDocument", [
            "source" => $js
        ]);

        $this->driver->get("https://www.tiktok.com/@tiktok");
        if ($this->verifyFp !== "") {
            $cookie = new Cookie("s_v_web_id", $this->verifyFp);
            $cookie->setDomain(".tiktok.com");
            $cookie->setSecure(true);
            $this->driver->manage()->addCookie($cookie);
        }

        // Wait until window.byted_acrawler is ready
        (new WebDriverWait($this->driver, 15))->until(function () {
            return $this->driver->executeScript("return window.byted_acrawler !== undefined && this.byted_acrawler.frontierSign !== undefined");
        });
    }
}
