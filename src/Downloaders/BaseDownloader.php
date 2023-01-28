<?php
namespace TikScraper\Downloaders;

use TikScraper\Constants\UserAgents;
use TikScraper\Traits\ProxyTrait;

abstract class BaseDownloader {
    use ProxyTrait;

    protected const BUFFER_SIZE = 256 * 1024;
    protected string $userAgent;

    function __construct(array $config = []) {
        $this->initProxy($config['proxy'] ?? []);
        $this->userAgent = $config['user_agent'] ?? UserAgents::DEFAULT;
    }
}
