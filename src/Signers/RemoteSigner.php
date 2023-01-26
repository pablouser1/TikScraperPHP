<?php
namespace TikScraper\Signers;

use TikScraper\Interfaces\SignerInterface;

class RemoteSigner implements SignerInterface {
    private string $url = '';

    public function __construct(array $config = []) {
        $this->url = $config['url'] ?? '';
    }

    public function run(string $unsigned_url): ?object {
        $ch = curl_init($this->url);
        curl_setopt_array($ch, [
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $unsigned_url,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Content-Type: text/plain'
            ]
        ]);
        $data = curl_exec($ch);
        $error = curl_errno($ch);
        curl_close($ch);
        if (!$error) {
            $data_json = json_decode($data);
            return $data_json;
        }
        return null;
    }
}
