<?php
namespace TikScraper;

use TikScraper\Constants\UserAgents;

class Stream {
    private int $buffer_size = 256 * 1024;
    // Headers to forward back to client, to be filled with response header values from TikTok
    private array $headers_to_forward = [
        'Content-Type' => null,
        'Content-Length' => null,
        'Content-Range' => null,
        // Always send this one to explicitly say we accept ranged requests
        'Accept-Ranges' => 'bytes'
    ];

    public function url(string $url) {
        $ch = curl_init($url);

        $headers_to_send = [];
        if (isset($_SERVER['HTTP_RANGE'])) {
            $headers_to_send[] = 'Range: ' . $_SERVER['HTTP_RANGE'];
            http_response_code(206);
        }

        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => $headers_to_send,
            CURLOPT_BUFFERSIZE => $this->buffer_size,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_USERAGENT => UserAgents::DEFAULT,
            CURLOPT_REFERER => "https://www.tiktok.com/discover",
            CURLOPT_HEADERFUNCTION => function ($curl, $header) {
                $len = strlen($header);
                $header = explode(':', $header, 2);
                $header_key = ucwords(trim($header[0]), '-');
                if (array_key_exists($header_key, $this->headers_to_forward)) {
                    $header_value = trim($header[1]);
                    $this->headers_to_forward[$header_key] = $header_value;
                }
                return $len;
            }
        ]);
        $response = curl_exec($ch);
        foreach ($this->headers_to_forward as $header_key => $header_value) {
            if ($header_value != null) {
                header($header_key . ': ' . $header_value);
            }
        }
        echo $response;
        curl_close($ch);
    }
}
