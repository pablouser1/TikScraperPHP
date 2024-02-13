<?php
namespace TikScraper\Models;

use TikScraper\Constants\Codes;

/**
 * Has information about how the request went
 * @param bool $success Request was successfull or not. True if $http_code is >= 200 and < 300 and $tiktok_code is 0
 * @param int $httpCode HTTP Code response
 * @param int $proxitokCode TikTok/ProxiTok's own error messages
 * @param string $proxitokMsg Detailed error message for $proxitokCode
 * @param Response $response Original response for debugging purposes
 */
class Meta {
    // These keys in the rehidrateState payload potentially contain the metadata
    // we require. They are checked in the order in which they are defined in
    // this array.
    private array $rehidrateKeys = [
        'webapp.video-detail',
        'webapp.user-detail',
        'webapp.music-detail',
    ];

    public bool $success = false;
    public int $httpCode = 503;
    public int $proxitokCode = -1;
    public string $proxitokMsg = '';
    public Response $response;
    public object $og;

    function __construct(Response $res) {
        $this->response = $res;
        $proxitokCode = -1;
        $proxitokMsg = '';
        if ($res->origRes !== null) {
            // Request was at least made by now
            $body = $res->origRes->getBody();
            if ($body->getSize() === 0) {
                // Response is empty
                $proxitokCode = 10;
            } elseif ($res->isJson) {
                // JSON Data
                if ($res->jsonBody !== null) {
                    if (isset($res->jsonBody->shareMeta)) {
                        $this->setOgIfExists($res->jsonBody);
                    }

                    $proxitokCode = $this->getCode($res->jsonBody);
                    $proxitokMsg = $this->getMsg($res->jsonBody);
                } else {
                    // Couldn't decode JSON
                    $proxitokCode = 11;
                }
            } elseif ($res->isHtml) {
                // HTML Data
                $scope = $res->rehidrateState->__DEFAULT_SCOPE__;

                // Setting code and OG from rehidrate
                if ($res->hasRehidrate()) {
                    $scope = $res->rehidrateState->__DEFAULT_SCOPE__;
                    $root = null;

                    // Search for valid root
                    foreach ($this->rehidrateKeys as &$key) {
                        if (isset($res->rehidrateState->__DEFAULT_SCOPE__->{$key})) {
                            $root = $res->rehidrateState->__DEFAULT_SCOPE__->{$key};
                            break;
                        }
                    }
                    unset($key);

                    $this->setOgIfExists($root);

                    $proxitokCode = $root->statusCode;
                    $proxitokMsg = $root->statusMsg;
                } else {
                    // Request doesn't have state data
                    $proxitokCode = 12;
                }
            }
        } else {
            // Couldn't make the request
            $proxitokCode = 21;
        }

        // Setting values
        $this->success = $res->http_success && $proxitokCode === 0;
        $this->httpCode = $res->code;
        $this->proxitokCode = $proxitokCode;
        $this->proxitokMsg = $proxitokMsg === '' ? Codes::fromId($proxitokCode) : $proxitokMsg;
    }

    private function getCode(object $data): int {
        $code = -1;
        if (isset($data->statusCode)) {
            $code = intval($data->statusCode);
        } elseif (isset($data->status_code)) {
            $code = intval($data->status_code);
        } elseif (isset($data->type) && $data->type === "verify") {
            // Check verify
            $code = 10000;
        }
        return $code;
    }

    private function getMsg(object $data): string {
        $msg = '';
        if (isset($data->statusMsg)) {
            $msg = $data->statusMsg;
        } elseif (isset($data->status_msg)) {
            $msg = $data->status_msg;
        }
        return $msg;
    }

    private function setOgIfExists(object $root) {
        if (isset($root->shareMeta)) {
            $this->og = new \stdClass;
            $this->og->title = $root->shareMeta->title;
            $this->og->description = $root->shareMeta->desc;
        }
    }
}
