<?php
namespace TikScraper\Wrappers;
use Facebook\WebDriver\Chrome\ChromeDevToolsDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Cookie;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\DriverCommand;
use Facebook\WebDriver\Remote\HttpCommandExecutor;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\WebDriverCommand;
use Facebook\WebDriver\WebDriverWait;
use SapiStudio\SeleniumStealth\SeleniumStealth;
use TikScraper\Helpers\Tokens;

class Selenium {
    private const DEFAULT_DRIVER_URL = "http://localhost:4444";
    private const DEFAULT_TIKTOK_URL = "https://www.tiktok.com/";

    private RemoteWebDriver $driver;

    function __construct(array $config, Tokens $tokens) {
        $debug = isset($config["debug"]) ? boolval($config["debug"]) : false;
        $browser = $config["browser"] ?? [
            "url" => self::DEFAULT_DRIVER_URL,
            "close_when_done" => false
        ];

        // Chrome flags
        $opts = new ChromeOptions();
        if (!$debug) {
            // Enable headless if not debugging
            $opts->addArguments(["--headless"]);
        }

        // User agent
        if (isset($config["user_agent"])) {
            $agent = $config["user_agent"];
            $opts->addArguments(["--user-agent=$agent"]);
        }

        $cap = DesiredCapabilities::chrome();
        $cap->setCapability(ChromeOptions::CAPABILITY_W3C, $opts);

        // Get session
        $executor = new HttpCommandExecutor($browser["url"], null, null);
        $executor->setConnectionTimeout(30000);
        $command = new WebDriverCommand(
            null,
            DriverCommand::GET_ALL_SESSIONS,
            []
        );

        $sessions = $executor->execute($command)->getValue();

        if (count($sessions) > 0) {
            // Reuse session
            $this->driver = RemoteWebDriver::createBySessionID($sessions[0]["id"], $browser["url"], null, null, true, $cap);
        } else {
            $this->_buildSeleniumSession($browser["url"], $cap, $tokens);
        }

        if ($tokens->getDeviceId() === "") {
            // Get Device Id from localStorage
            $sess_id = $this->driver->executeScript('return sessionStorage.getItem("webapp_session_id")');
            if ($sess_id !== null) {
                $tokens->setDeviceId(substr($sess_id, 0, 19));
            }
        }
    }

    public function getDriver(): RemoteWebDriver {
        return $this->driver;
    }

    public function getNavigator(): object {
        return (object) $this->driver->executeScript("return {
            user_agent: window.navigator.userAgent,
            browser_language: window.navigator.language,
            browser_platform: window.navigator.platform,
            browser_name: window.navigator.appCodeName,
            browser_version: window.navigator.appVersion
        }");
    }

    public function getUserAgent(): string {
        return $this->getNavigator()->user_agent;
    }

    private function _buildSeleniumSession(string $url, DesiredCapabilities $cap, Tokens $tokens): void {
        $js = file_get_contents(__DIR__ . "/../../js/fetch.js");
        // Create session
        $tmpDriver = RemoteWebDriver::create($url, $cap);
        $this->driver = (new SeleniumStealth($tmpDriver))->usePhpWebriverClient()->makeStealth();

        // Inject custom JS code for fetching TikTok's API
        $devTools = new ChromeDevToolsDriver($this->driver);
        $devTools->execute("Page.addScriptToEvaluateOnNewDocument", [
            "source" => $js
        ]);

        $this->driver->get(self::DEFAULT_TIKTOK_URL);

        // Add captcha cookie to Selenium's jar
        if ($tokens->getVerifyFp() !== '') {
            $cookie = new Cookie("s_v_web_id", $tokens->getVerifyFp());
            $cookie->setDomain(".tiktok.com");
            $cookie->setSecure(true);
            $this->driver->manage()->addCookie($cookie);
        }

        // Wait until window.byted_acrawler is ready or timeout
        (new WebDriverWait($this->driver, 10))->until(function () {
            return $this->driver->executeScript("return window.byted_acrawler !== undefined && this.byted_acrawler.frontierSign !== undefined");
        });
    }
}
