<?php
require __DIR__ . '/../vendor/autoload.php';

function getStandardApi(string $browser_url = 'http://localhost:4444'): \TikScraper\Api {
    return new \TikScraper\Api([
        'signer' => [
            'browser_url' => $browser_url
        ]
    ]);
}
