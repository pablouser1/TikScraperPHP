<?php
namespace TikScraper\Helpers;

/**
 * Class that stores relevant tokens for TikTok's API requests
 *
 * Gets its data from `$config` or from Selenium data
 */
class Tokens {
    private string $verifyFp = "";
    private string $device_id = "";

    function __construct(array $config) {
        $this->verifyFp = $config["verify_fp"] ?? "";
        $this->device_id = $config["device_id"] ?? "";
    }

    public function getVerifyFp(): string {
        return $this->verifyFp;
    }

    public function setVerifyFp(string $verifyFp): void {
        $this->verifyFp = $verifyFp;
    }

    public function getDeviceId(): string {
        return $this->device_id;
    }

    public function setDeviceId(string $device_id): void {
        $this->device_id = $device_id;
    }
}
