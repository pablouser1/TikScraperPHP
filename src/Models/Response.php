<?php
namespace TikScraper\Models;

class Response {
    public bool $http_success;
    public int $code;
    public mixed $data;

    function __construct(bool $http_code, int $code, mixed $data) {
        $this->http_success = $http_code;
        $this->code = $code;
        $this->data = $data;
    }
}
