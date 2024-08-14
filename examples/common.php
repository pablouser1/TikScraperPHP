<?php
use TikScraper\Api;
use TikScraper\Models\Meta;

define("TIKTOK_DEBUG", isset($_SERVER["TIKTOK_DEBUG"]) ? boolval($_SERVER["TIKTOK_DEBUG"]) : false);
define("TIKTOK_VERIFYFP", $_SERVER["TIKTOK_VERIFYFP"] ?? "");

function buildApi(): Api {
    return new Api([
        "debug" => TIKTOK_DEBUG,
        "verify_fp" => TIKTOK_VERIFYFP
    ]);
}

function printError(Meta $meta) {
    echo "Error processing request!\n";
    echo "HTTP " . $meta->httpCode . "\n";
    echo "Code " . $meta->proxitokCode . " (" . $meta->proxitokMsg . ")";
}
