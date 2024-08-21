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
use TikScraper\Helpers\Tokens;

class Selenium {
    private const DEFAULT_DRIVER_URL = "http://localhost:4444";
    private const DEFAULT_TIKTOK_URL = "https://www.tiktok.com/feedback/";

    private const SPOOF_JS = [
        "utils.js",
        "chrome.app.js", "chrome.csi.js", "chrome.loadtimes.js", "chrome.runtime.js",
        "iframe.contentWindow.js",
        "media.codecs.js",
        "navigator.hardwareConcurrency.js", "navigator.languages.js", "navigator.permissions.js",
        "navigator.plugins.js", "navigator.vendor.js", "navigator.webdriver.js",
        "webgl.vendor.js",
        "window.outerdimensions.js"
    ];

    private RemoteWebDriver $driver;

    function __construct(array $config, Tokens $tokens) {
        $debug = isset($config["debug"]) ? boolval($config["debug"]) : false;
        $browser = $config["browser"] ?? [
            "url" => self::DEFAULT_DRIVER_URL,
            "close_when_done" => false
        ];

        $args = ["--disable-blink-features=AutomationControlled"];

        // Chrome flags
        $opts = new ChromeOptions();
        $opts->setExperimentalOption("excludeSwitches", [
            "enable-automation",
            "disable-extensions",
            "disable-default-apps",
            "disable-component-extensions-with-background-pages"
        ]);

        if (!$debug) {
            // Enable headless if not debugging
            $args[] = "--headless=new";
        }

        // User defined user agent
        if (isset($config["user_agent"]) && !empty($config["user_agent"])) {
            $agent = $config["user_agent"];
            $args[] = "--user-agent=$agent";
        }

        // Proxy
        if (isset($config['proxy']) && !empty($config["proxy"])) {
            $proxy = $config['proxy'];
            $args[] = "--proxy-server=$proxy";
        }

        if (count($args) > 0) {
            $opts->addArguments($args);
        }

        $cap = DesiredCapabilities::chrome();
        $cap->setCapability(ChromeOptions::CAPABILITY, $opts);

        // Get sessionç
        $sessions = $this->_getSessions($browser["url"]);
        if (count($sessions) > 0) {
            // Reuse session
            $this->driver = RemoteWebDriver::createBySessionID($sessions[0]["id"], $browser["url"], null, null, true, $cap);
        } else {
            // Build new session
            $this->_buildSession($browser["url"], $cap, $tokens);
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

    /**
     * Build selenium session, executes only on first run.
     * Waits until `window.byted_acrawler` is available or timeout
     * @param string $url Chromedriver url
     * @param \Facebook\WebDriver\Remote\DesiredCapabilities $cap Chrome's capabilities
     * @param \TikScraper\Helpers\Tokens $tokens
     * @return void
     */
    private function _buildSession(string $url, DesiredCapabilities $cap, Tokens $tokens): void {
        // Create session
        $this->driver = RemoteWebDriver::create($url, $cap);

        $devTools = new ChromeDevToolsDriver($this->driver);
        $this->_spoof($devTools);

        $fetch = file_get_contents(__DIR__ . "/../../js/fetch.js");
        // Inject custom JS code for fetching TikTok's API
        $devTools->execute("Page.addScriptToEvaluateOnNewDocument", [
            "source" => $fetch
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

    private function _getSessions(string $url): array {
        $executor = new HttpCommandExecutor($url, null, null);
        $executor->setConnectionTimeout(30000);
        $command = new WebDriverCommand(
            null,
            DriverCommand::GET_ALL_SESSIONS,
            []
        );

        return $executor->execute($command)->getValue();
    }

    private function _spoof(ChromeDevToolsDriver $devTools): void {
        foreach (self::SPOOF_JS as $js) {
            $js_str = file_get_contents(__DIR__ . '/../../js/stealth/' . $js);
            if ($js_str !== false) {
                $devTools->execute("Page.addScriptToEvaluateOnNewDocument", [
                    "source" => $js_str
                ]);
            }
        }
        $this->_spoofUa($devTools);
    }

    private function _spoofUa(ChromeDevToolsDriver $devTools): void {
        $ua = $this->getUserAgent();
        $ua = str_replace("HeadlessChrome", "Chrome", $ua);

        // Spoof Linux
        if (str_contains($ua, "Linux") && !str_contains($ua, "Android")) {
            $ua = preg_replace("/\(([^)]+)\)/", '(Windows NT 10.0; Win64; x64)', $ua);
        }

        // Get version
        $uaVersion = "";
        if (str_contains($ua, "Chrome")) {
            $matches = [];
            preg_match("/Chrome\/([\d|.]+)/", $ua, $matches);
            $uaVersion = $matches[1];
        } else {
            $matches = [];
            preg_match("/\/([\d|.]+)/", $this->driver->getCapabilities()->getVersion(), $matches);
        }

        // Get platform
        $platform = '';
        if (str_contains('Mac OS X', $ua)) {
            $platform = 'Mac OS X';
        } else if (str_contains('Android', $ua)) {
            $platform = 'Android';
        } else if (str_contains('Linux', $ua)) {
            $platform = 'Linux';
        } else {
            $platform = 'Windows';
        }

        // Get brands
        $seed = explode('.', $uaVersion)[0]; // Major chrome version
        $order = [
            [0, 1, 2],
            [0, 2, 1],
            [1, 0, 2],
            [1, 2, 0],
            [2, 0, 1],
            [2, 1, 0]
        ][$seed % 6];

        $escapedChars = [' ', ' ', ';'];

        $char1 = $escapedChars[$order[0]];
        $char2 = $escapedChars[$order[1]];
        $char3 = $escapedChars[$order[2]];

        $greaseyBrand = "{$char1}Not{$char2}A{$char3}Brand";
        $greasedBrandVersionList = [];

        $greasedBrandVersionList[$order[0]] = [
            "brand" => $greaseyBrand,
            "version" => "99"
        ];

        $greasedBrandVersionList[$order[1]] = [
            "brand" => "Chromium",
            "version" => $seed
        ];

        $greasedBrandVersionList[$order[2]] = [
            "brand" => "Google Chrome",
            "version" => $seed
        ];

        $os_version = '';
        if (str_contains('Mac OS X ', $ua)) {
            $matches = [];
            preg_match("/Mac OS X ([^)]+)/", $ua, $matches);

            $os_version = $matches[1];
        } else if (str_contains('Android ', $ua)) {
            $matches = [];
            preg_match("/Android ([^;]+)/", $ua, $matches);

            $os_version = $matches[1];
        } else if (str_contains('Windows ', $ua)) {
            $matches = [];
            preg_match("/Windows .*?([\d|.]+);?/", $ua, $matches);

            $os_version = $matches[1];
        }

        $arch = '';
        $model = '';
        $mobile = str_contains('Android', $ua);
        if ($mobile) {
            $matches = [];

            preg_match("/Android.*?;\s([^)]+)/", $ua, $matches);
            $model = $matches[1];
        } else {
            $arch = 'x86';
        }

        $ua_rewrite = [
            'userAgent' => $ua,
            'acceptLanguage' => 'en-US,en',
            'platform' => $platform
        ];

        $devTools->execute('Emulation.setUserAgentOverride', $ua_rewrite);
    }
}
