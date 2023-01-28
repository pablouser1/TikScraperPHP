<?php
namespace TikScraper\Signers;

use TikScraper\Constants\UserAgents;
use TikScraper\Interfaces\SignerInterface;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use SapiStudio\SeleniumStealth\SeleniumStealth;

class BrowserSigner implements SignerInterface {
    const DEFAULT_URL = 'https://tiktok.com/@tiktok';
    const PASSWORD = 'webapp1.0+202106';
    private string $url = '';
    private bool $closeWhenDone = true;
    private RemoteWebDriver $driver;

    function __construct(array $config = []) {
        $this->url = $config['url'] ?? '';
        $this->closeWhenDone = $config['close_when_done'] ?? true;
        $this->__setupSelenium($this->url);
    }

    function __destruct() {
        if ($this->closeWhenDone) $this->driver->quit();
    }

    public function run(string $unsigned_url): ?object {
        $signature = $this->driver->executeScript('return window.byted_acrawler.sign(arguments[0])', [
            ['url' => $unsigned_url]
        ]);

        $signed_url = $unsigned_url . '&_signature=' . $signature;
        # Get params of url as string
        $params_str = parse_url($signed_url, PHP_URL_QUERY);
        $xttparams = $this->__xttparams($params_str);

        return (object) [
            'status' => 'ok',
            'data' => (object) [
                'signature' => $signature,
                'signed_url' => $signed_url,
                'x-tt-params' => $xttparams,
                'navigator' => $this->__navigator()
            ]
        ];
    }

    private function __setupSelenium(string $browser_url) {
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

    private function __navigator(): object {
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

    private function __xttparams(string $params): string {
        return openssl_encrypt($params, 'aes-128-cbc', self::PASSWORD, 0, self::PASSWORD);
    }
}
