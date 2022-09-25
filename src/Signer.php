<?php

namespace TikScraper;

use TikScraper\Interfaces\SignerInterface;
use TikScraper\Constants\SignMethods;
use TikScraper\Signers\BrowserSigner;
use TikScraper\Signers\RemoteSigner;

class Signer {
    private SignerInterface $client;

    function __construct(array $config) {
        $method = $config['method'] ?? '';
        $this->client = $this->__setSigner($method, $config);
    }

    /**
     * Picks remote or local signing depending on the config passed to this class
     */
    public function run(string $unsigned_url): ?object {
        return $this->client ? $this->client->run($unsigned_url) : null;
    }

    private function __setSigner(string $method, array $config): ?SignerInterface {
        $class_str = '';
        switch ($method) {
            case SignMethods::BROWSER:
                $class_str = BrowserSigner::class;
                break;
            case SignMethods::REMOTE:
                $class_str = RemoteSigner::class;
                break;
        }

        return $class_str ? new $class_str($config) : null;
    }
}
