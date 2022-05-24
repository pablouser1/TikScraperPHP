<?php

namespace TikScraper\Helpers;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use SapiStudio\SeleniumStealth\SeleniumStealth;
use TikScraper\Constants\UserAgents;

class Signer {
    const DEFAULT_URL = 'https://tiktok.com/@tiktok';
    const PASSWORD = 'webapp1.0+202106';
    // Remote signing
    private $remote_url = '';
    // Selenium
    private RemoteWebDriver $driver;
    private $browser_url = '';

    /**
     * Close browser when finished
     */
    private bool $close_when_done = true;

    function __construct(array $config) {
        $remote_url = isset($config['remote_url']) ? $config['remote_url'] : '';
        $browser_url = isset($config['browser_url']) ? $config['browser_url'] : '';
        $close_when_done = isset($config['close_when_done']) ? $config['close_when_done'] : true;
        if ($remote_url) {
            $this->remote_url = $remote_url;
        } elseif ($browser_url) {
            $this->close_when_done = $close_when_done;
            $this->browser_url = $browser_url;
            $this->setupSelenium($browser_url);
        }
    }

    function __destruct() {
        if ($this->browser_url && $this->close_when_done) $this->driver->quit();
    }

    private function setupSelenium(string $browser_url) {
        // Check existing sessions
        $sessions = RemoteWebDriver::getAllSessions($browser_url);
        if (!empty($sessions)) {
            // Use first session that already exists
            $this->driver = RemoteWebDriver::createBySessionID($sessions[0]['id'], $browser_url);
            $this->driver = (new SeleniumStealth($this->driver))->usePhpWebriverClient()->makeStealth();
        } else {
            // Create session
            $chromeOptions = new ChromeOptions();
            $chromeOptions->addArguments([
                '--headless',
                '--disable-gpu',
                '--no-sandbox',
                '--disable-blink-features=AutomationControlled',
                '--user-agent=' . UserAgents::DEFAULT
            ]);
            $chromeOptions->setExperimentalOption('excludeSwitches', ['enable-automation']);

            // Capabilities
            $capabilities = DesiredCapabilities::chrome();
            $capabilities->setCapability(ChromeOptions::CAPABILITY_W3C, $chromeOptions);
            $this->driver = RemoteWebDriver::create($browser_url, $capabilities);
            // Stealth mode
            $this->driver = (new SeleniumStealth($this->driver))->usePhpWebriverClient()->makeStealth();

            // Go to page
            $this->driver->get(self::DEFAULT_URL);
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('app')));

            // Load scripts
            $signature = file_get_contents(__DIR__ . '/../../js/signature.js');
            $this->driver->executeScript($signature);
        }
    }

    private function navigator(): object {
        $script = <<<'EOD'
        return {
            deviceScaleFactor: window.devicePixelRatio,
            user_agent: window.navigator.userAgent,
            browser_language: window.navigator.language,
            browser_platform: window.navigator.platform,
            browser_name: window.navigator.appCodeName,
            browser_version: window.navigator.appVersion,
        }
        EOD;
        $info = $this->driver->executeScript($script);
        return (object) $info;
    }

    /**
     * Sign url using local chromedriver
     */
    private function browser(string $url): object {
        $verifyfp = Misc::verify_fp();
        $url .= '&verifyFp=' . $verifyfp;

        $signature = $this->driver->executeScript('return window.byted_acrawler.sign(arguments[0])', [
            ['url' => $url]
        ]);

        $signed_url = $url . '&_signature=' . $signature;
        # Get params of url as string
        $params_str = parse_url($signed_url, PHP_URL_QUERY);
        $xttparams = $this->xttparams($params_str);
        return (object) [
            'status' => 'ok',
            'data' => (object) [
                'signature' => $signature,
                'verify_fp' => $verifyfp,
                'signed_url' => $signed_url,
                'x-tt-params' => $xttparams,
                'navigator' => $this->navigator()
            ]
        ];
    }

    /**
     * Sign url using remote server
     */
    private function remote(string $url): ?object {
        $ch = curl_init($this->remote_url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $url,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Content-Type: text/plain'
            ]
        ]);
        $data = curl_exec($ch);
        $error = curl_errno($ch);
        curl_close($ch);
        if (!$error) {
            $data_json = json_decode($data);
            return $data_json;
        }
        return null;
    }

    private function xttparams(string $params): string {
        $crypt = openssl_encrypt($params, 'aes-128-cbc', self::PASSWORD, 0, self::PASSWORD);
        return $crypt;
    }

    /**
     * Picks remote or local signing depending on the config passed to this class
     */
    public function run(string $url): object {
        if ($this->remote_url) {
            return $this->remote($url);
        } elseif ($this->browser_url) {
            return $this->browser($url);
        }
        throw new \Exception('You are running this wrapper on Standard mode without a local or remote signer!', 500);
    }
}
