<?php
function initApi(): \TikScraper\Api {
    $verifyfp = $_SERVER["TIKTOK_VERIFYFP"] ?? "";
    $chromedriver = $_SERVER["TIKTOK_CHROMEDRIVER"] ?? "http://localhost:4444";
    $api = new \TikScraper\Api([
        "debug" => true,
        "verify_fp" => $verifyfp,
        "chromedriver" => $chromedriver
    ]);
    return $api;
}

function randStr(): string {
    return bin2hex(random_bytes(16));
}
