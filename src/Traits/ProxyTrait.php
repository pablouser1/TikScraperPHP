<?php
namespace TikScraper\Traits;

/**
 * Proxy config for TikTok requests
 */
trait ProxyTrait {
    private array $proxy = [];

    protected function initProxy(array $proxy = []): void {
        $this->proxy = $proxy;
    }

    protected function setProxy(&$ch): void {
        if (isset($this->proxy['host'], $this->proxy['port'])) {
            curl_setopt($ch, CURLOPT_PROXY, $this->proxy['host'] . ":" . $this->proxy['port']);
            if (isset($this->proxy['username'], $this->proxy['password'])) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->proxy['username'] . ":" . $this->proxy['password']);
            }
            curl_setopt($ch, CURLOPT_NOPROXY, '127.0.0.1,localhost');
        }
    }
}
