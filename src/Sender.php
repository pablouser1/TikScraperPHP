<?php
namespace TikScraper;

use TikScraper\Helpers\Curl;
use TikScraper\Helpers\Misc;
use TikScraper\Helpers\Request;
use TikScraper\Models\Response;

class Sender {
    private const REFERER = 'https://www.tiktok.com/foryou';
    private const DEFAULT_HEADERS = [
        "authority: m.tiktok.com",
        "method: GET",
        "scheme: https",
        "accept: application/json, text/plain, */*",
        "accept-encoding: gzip, deflate, br",
        "accept-language: en-US,en;q=0.9",
        "origin:" . self::REFERER,
        "referer:" . self::REFERER,
        "sec-fetch-dest: empty",
        "sec-fetch-mode: cors",
        "sec-fetch-site: same-site",
        "sec-gpc: 1"
    ];

    private Storage $storage;

    private $remote_signer = 'http://localhost:8080/signature';
    private $proxy = [];

    function __construct(array $config) {
        if (isset($config['remote_signer'])) $this->remote_signer = $config['remote_signer'];
        if (isset($config['proxy'])) $this->proxy = $config['proxy'];

        $this->storage = new Storage($config['storage_file'] ?? sys_get_temp_dir() . '/tiktok.json');
        if (!isset($this->storage->cookies['csrf_session_id'], $this->storage->headers['csrf_token'])) {
            $extra = $this->getInfo('https://www.tiktok.com');
            $this->storage->cookies['csrf_session_id'] = $extra['csrf_session_id'];
            $this->storage->headers['csrf_token'] = $extra['csrf_token'];
        }
    }

    // -- Extra -- //
    public function remoteSign(string $url) {
        $ch = curl_init($this->remote_signer);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_USERAGENT => Common::DEFAULT_USERAGENT,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $url,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ]
        ]);
        Curl::handleProxy($ch, $this->proxy);
        $data = curl_exec($ch);
        $data_json = json_decode($data);
        return $data_json;
    }

    public function sendHead(string $url, array &$headers = [], array $req_headers = []) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_NOBODY => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => Common::DEFAULT_USERAGENT,
            CURLOPT_ENCODING => 'utf-8',
            CURLOPT_AUTOREFERER => true,
            CURLOPT_HTTPHEADER => $req_headers
        ]);

        curl_setopt($ch, CURLOPT_HEADERFUNCTION,
            function ($ch, $header) use ($headers) {
                $len = strlen($header);
                $header = explode(':', $header, 2);
                if (count($header) < 2) // ignore invalid headers
                    return $len;

                $headers[strtolower(trim($header[0]))][] = trim($header[1]);

                return $len;
            }
        );

        Curl::handleProxy($ch, $this->proxy);

        $data = curl_exec($ch);
        return $data;
    }

    public function getInfo(string $url): array {
        $headers = [];
        $data = $this->sendHead($url, $headers, [
            "x-secsdk-csrf-version: 1.2.5",
            "x-secsdk-csrf-request: 1"
        ]);
        $cookies = Curl::extractCookies($data);

        $csrf_session_id = isset($cookies['csrf_session_id']) ? $cookies['csrf_session_id'] : '';
        $csrf_token = isset($headers['x-ware-csrf-token'][0]) ? explode(',', $headers['x-ware-csrf-token'][0])[1] : '';

        return [
            'csrf_session_id' => $csrf_session_id,
            'csrf_token' => $csrf_token
        ];
    }

    public function sendGet(
        string $endpoint,
        string $subdomain = 'm',
        array $query = [],
        bool $isApi = true,
        bool $send_tt_params = false,
        string $ttwid = ''
    ): Response {
        $headers = [];
        $cookies = '';
        $ch = curl_init();
        $url = 'https://' . $subdomain . '.tiktok.com' . $endpoint . '/';
        $useragent = Common::DEFAULT_USERAGENT;

        if ($isApi) {
            $device_id = Misc::makeId();
            $verifyFp = Misc::verify_fp();
            $query['device_id'] = $device_id;
            $query['verifyFp'] = $verifyFp;
            // URL to send to signer
            $signing_url = $url . Request::buildQuery($query);
            $signer_res = $this->remoteSign($signing_url);
            $url = $signer_res->data->signed_url;

            $useragent = $signer_res->data->navigator->user_agent;

            if ($send_tt_params) {
                $headers[] = 'x-tt-params: ' . $signer_res->data->{'x-tt-params'};
            }

            if ($ttwid) {
                $cookies .= 'ttwid=' . $ttwid . ';';
            }

            // Extra
            if ($subdomain === 'm') {
                $headers[] = 'x-secsdk-csrf-token:' . $this->storage->headers['csrf_token'];
                $cookies .= Request::getCookies($device_id, $this->storage->cookies['csrf_session_id']);
            }
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => $useragent,
            CURLOPT_ENCODING => "utf-8",
            CURLOPT_AUTOREFERER => true,
            CURLOPT_COOKIE => $cookies,
            CURLOPT_HTTPHEADER => array_merge($headers, self::DEFAULT_HEADERS)
        ]);
        Curl::handleProxy($ch, $this->proxy);
        $data = curl_exec($ch);
        if (!curl_errno($ch)) {
            // Request sent
            $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            return new Response($code >= 200 && $code < 400, $code, $isApi ? json_decode($data) : $data);
        }
        return new Response(false, 503, '');
    }
}
