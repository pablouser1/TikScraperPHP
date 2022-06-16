<?php
use PHPUnit\Framework\TestCase;
use TikScraper\Api;

class DefaultTest extends TestCase {
    const DEFAULT_SIGNER = 'https://signtok.herokuapp.com';
    protected Api $api;

    function __construct(?string $name = null, array $data = [], $dataName = '') {
        parent::__construct($name, $data, $dataName);
        $this->api = new Api([
            'signer' => [
                'remote_url' => self::DEFAULT_SIGNER
            ]
        ]);
    }
}
