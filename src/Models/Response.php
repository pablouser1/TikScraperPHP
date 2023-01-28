<?php
namespace TikScraper\Models;

class Response {
    public bool $http_success;
    public int $code;
    public $data;

    function __construct(int $code, $data) {
        $this->http_success = $code >= 200 && $code < 400;
        $this->code = $code;
        $this->data = $data;
    }
}
