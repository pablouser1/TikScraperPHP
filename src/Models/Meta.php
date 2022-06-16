<?php
namespace TikScraper\Models;

use TikScraper\Constants\Codes;

class Meta {
    public bool $success = false;
    public int $http_code = 503;
    public int $tiktok_code = 0;
    public string $tiktok_msg = '';

    function __construct(bool $http_success, int $code, $data) {
        $keys = array_keys(Codes::list);
        $http_success = $http_success;
        $http_code = $code;

        if (is_object($data)) {
            $tiktok_code = $this->getCode($data);
        } elseif ($data === "") {
            $tiktok_code = -1;
        } else {
            $tiktok_code = 0;
        }

        $tiktok_msg = in_array($tiktok_code, $keys) ? Codes::list[$tiktok_code] : 'Unknown error';

        // Setting values
        $this->success = $http_success && $tiktok_code === 0;
        $this->http_code = $http_code;
        $this->tiktok_code = $tiktok_code;
        $this->tiktok_msg = $tiktok_msg;
    }

    private function getCode(object $data): int {
        if (isset($data->statusCode)) return (int) $data->statusCode;
        if (isset($data->status_code)) return (int) $data->status_code;
    }
}
