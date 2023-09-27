<?php
namespace TikScraper\Downloaders;

use TikScraper\HTTPClient;

abstract class BaseDownloader {
    protected const BUFFER_SIZE = 1024;
    protected HTTPClient $httpClient;

    function __construct(array $config = []) {
        $this->httpClient = new HTTPClient($config);
    }
}
