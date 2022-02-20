<?php
namespace TikScraper\Models;

class Response {
    public bool $http_success;
    public int $code;
    public $data;

    function __construct(bool $http_success, int $code, $data) {
        $this->http_success = $http_success;
        $this->code = $code;
        $this->data = $data;
    }
}
