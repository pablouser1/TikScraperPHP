<?php
namespace TikScraper;

use TikScraper\Common;

class Stream {
    protected $buffer_size = 256 * 1024;
    protected $headers = [];
    protected $headers_sent = false;

    public function bodyCallback($ch, $data) {
        echo $data;
        flush();
        return strlen($data);
    }

    public function headerCallback($ch, $data) {
        if (preg_match('/HTTP\/[\d.]+\s*(\d+)/', $data, $matches)) {
            $status_code = $matches[1];

            if (200 == $status_code || 206 == $status_code || 403 == $status_code || 404 == $status_code) {
                $this->headers_sent = true;
                $this->sendHeader(rtrim($data));
            }
        } else {

            $forward = ['content-type', 'content-length', 'accept-ranges', 'content-range'];

            $parts = explode(':', $data, 2);

            if ($this->headers_sent && count($parts) == 2 && in_array(trim(strtolower($parts[0])), $forward)) {
                $this->sendHeader(rtrim($data));
            }
        }

        return strlen($data);
    }

    public function stream($url) {
        $ch = curl_init($url);

        $headers = [];
        if (isset($_SERVER['HTTP_RANGE'])) {
            $headers[] = 'Range: ' . $_SERVER['HTTP_RANGE'];
        }

        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_BUFFERSIZE => $this->buffer_size,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_HEADER => false,
            CURLOPT_USERAGENT => Common::DEFAULT_USERAGENT,
            CURLOPT_REFERER => "https://www.tiktok.com/discover",
            CURLOPT_HEADERFUNCTION => [$this, 'headerCallback'],
            CURLOPT_WRITEFUNCTION => [$this, 'bodyCallback']
        ]);

        curl_exec($ch);
        curl_close($ch);
        return true;
    }

    protected function sendHeader($header) {
        header($header);
    }
}
