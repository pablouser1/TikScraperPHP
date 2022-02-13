<?php
namespace TikScraper\Models;

class Response {
    public bool $http_success;
    public int $code;
    public mixed $data;

    function __construct(bool $http_success, int $code, mixed $data) {
        $this->http_success = $http_success;
        $this->code = $code;
        $this->data = $data;
    }
}
