<?php
namespace TikScraper;
use GuzzleHttp\Exception\ConnectException;
use Psr\Http\Message\ResponseInterface;
use TikScraper\Helpers\Tokens;
use TikScraper\Wrappers\Guzzle;
use TikScraper\Wrappers\Selenium;

/**
 * Video streaming class.
 * Does chunked video streaming using `Range` header
 */
class Stream {
    private const BUFFER_SIZE = 1024;
    // Headers to forward back to client, to be filled with response header values from TikTok
    private array $headers_to_forward = [
        'Content-Type' => null,
        'Content-Length' => null,
        'Content-Range' => null,
        // Always send this one to explicitly say we accept ranged requests
        'Accept-Ranges' => 'bytes'
    ];

    private Tokens $tokens;
    private Selenium $selenium;
    private Guzzle $guzzle;

    public function __construct(array $config = []) {
        $this->tokens = new Tokens($config);
        $this->selenium = new Selenium($config, $this->tokens);
        $this->guzzle = new Guzzle($config, $this->selenium);
    }

    /**
     * Streams selected url
     * @param string $url
     * @return void
     */
    public function url(string $url): void {
        $client = $this->guzzle->getClient();

        $headers_to_send = [
            "Accept" => "video/webm,video/ogg,video/*;q=0.9,application/ogg;q=0.7,audio/*;q=0.6,*/*;q=0.5",
            "Accept-Language" => "en-US",
            "Referer" => "https://www.tiktok.com/",
            "DNT" => "1",
            "Sec-Fetch-Dest" => "video",
            "Sec-Fetch-Mode" => "cors",
            "Sec-Fetch-Site" => "same-site",
            "Accept-Encoding" => "identity"
        ];
        if (isset($_SERVER['HTTP_RANGE'])) {
            $headers_to_send['Range'] = $_SERVER['HTTP_RANGE'];
            http_response_code(206);
        }

        try {
            $res = $client->get($url, [
                "headers" => $headers_to_send,
                "http_errors" => false,
                "on_headers" => function (ResponseInterface $response) {
                    $headers = $response->getHeaders();
                    foreach ($headers as $key => $value) {
                        if (array_key_exists($key, $this->headers_to_forward)) {
                            $this->headers_to_forward[$key] = $value;
                        }
                    }
                },
                "stream" => true
            ]);

            $code = $res->getStatusCode();

            foreach ($this->headers_to_forward as $key => $value) {
                if ($value !== null) {
                    if (is_array($value)) {
                        foreach ($value as $currentVal) {
                            header($key . ': ' . $currentVal, false);
                        }
                    } else {
                        header($key . ': ' . $value, false);
                    }
                }
            }

            if ($code >= 400 && $code < 500) {
                http_response_code($code);
            }

            $body = $res->getBody();
            while (!$body->eof()) {
                echo $body->read(self::BUFFER_SIZE);
            }
        } catch (ConnectException $e) {
            die("Couldn't connect to TikTok!");
        }
    }
}
