<?php
function initApi(): \TikScraper\Api {
    $signer = isset($_ENV['API_SIGNER_URL']) ? $_ENV['API_SIGNER_URL'] : "https://signtok.vercel.app/api/signature";
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
