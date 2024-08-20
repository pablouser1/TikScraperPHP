<?php
namespace TikScraper\Downloaders;

use TikScraper\Guzzle;
use TikScraper\Selenium;

abstract class BaseDownloader {
    protected const BUFFER_SIZE = 1024;

    protected Selenium $selenium;
    protected Guzzle $guzzle;

    function __construct(array $config = []) {
        $this->selenium = new Selenium($config);
        $this->guzzle = new Guzzle($config);

        $this->guzzle->setUserAgent($this->selenium->getUserAgent());
    }
}
