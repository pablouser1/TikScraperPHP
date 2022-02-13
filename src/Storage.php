<?php
namespace TikScraper;

class Storage {
    private string $storage_file = '';
    public array $cookies = [];
    public array $headers = [];
    function __construct(string $storage_file) {
        $this->storage_file = $storage_file;
        if (is_file($this->storage_file)) {
            $storage_str = file_get_contents($this->storage_file);
            $storage = json_decode($storage_str, true);
            $this->cookies = $storage['cookies'];
            $this->headers = $storage['headers'];
        }
    }

    public function save() {
        if (!empty($this->cookies) && !empty($this->headers)) {
            $data = [
                'cookies' => $this->cookies,
                'headers' => $this->headers
            ];
            file_put_contents($this->storage_file, json_encode($data));
        }
    }
}
