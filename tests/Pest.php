<?php
const DEFAULT_SIGNER = "https://signtok.pabloferreiro.es";

function initApi(): \TikScraper\Api {
    $signer = isset($_ENV['API_SIGNER_URL']) ? $_ENV['API_SIGNER_URL'] : DEFAULT_SIGNER;
    $api = new \TikScraper\Api([
        'signer' => [
            'method' => 'remote',
            'url' => $signer
        ]
    ]);
    return $api;
}

function randStr(): string {
    return bin2hex(random_bytes(16));
}
