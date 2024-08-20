<?php
namespace TikScraper\Downloaders;
use TikScraper\Helpers\Tokens;
use TikScraper\Wrappers\Guzzle;
use TikScraper\Wrappers\Selenium;

abstract class BaseDownloader {
    protected const BUFFER_SIZE = 1024;

    protected Tokens $tokens;
    protected Selenium $selenium;
    protected Guzzle $guzzle;

    function __construct(array $config = []) {
        $this->tokens = new Tokens($config);
        $this->selenium = new Selenium($config, $this->tokens);
        $this->guzzle = new Guzzle($config, $this->selenium);
    }
}
