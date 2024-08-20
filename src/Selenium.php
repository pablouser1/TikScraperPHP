<?php
namespace TikScraper;
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

class Selenium {
    private string $verifyFp = "";
    private string $device_id = "";
    private RemoteWebDriver $driver;

    function __construct(array $config) {
        $this->verifyFp = $config["verify_fp"] ?? "";
        $this->device_id = $config["device_id"] ?? "";
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

        // Get session
        $executor = new HttpCommandExecutor($url, null, null);
        $executor->setConnectionTimeout(30000);
        $command = new WebDriverCommand(
            null,
            DriverCommand::GET_ALL_SESSIONS,
            []
        );

        $sessions = $executor->execute($command)->getValue();

        if (count($sessions) > 0) {
            // Reuse session
            $this->driver = RemoteWebDriver::createBySessionID($sessions[0]["id"], $url, null, null, true, $cap);
        } else {
            $this->_buildSeleniumSession($url);
        }

        if ($this->device_id === "") {
            // Get Device Id from localStorage
            $sess_id = $this->driver->executeScript('return sessionStorage.getItem("webapp_session_id")');
            if ($sess_id !== null) {
                $this->device_id = substr($sess_id, 0, 19);
            }
        }
    }

    public function getDriver(): RemoteWebDriver {
        return $this->driver;
    }

    public function getVerifyFp(): string {
        return $this->verifyFp;
    }

    public function getDeviceId(): string {
        return $this->device_id;
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
        (new WebDriverWait($this->driver, 10))->until(function () {
            return $this->driver->executeScript("return window.byted_acrawler !== undefined && this.byted_acrawler.frontierSign !== undefined");
        });
    }
}
