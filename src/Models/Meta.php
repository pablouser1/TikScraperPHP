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
    public bool $success = false;
    public int $httpCode = 503;
    public int $proxitokCode = -1;
    public string $proxitokMsg = '';
    public Response $response;
    public object $og;

    function __construct(Response $res) {
        $this->httpCode = $res->code;
        $this->response = $res;

        if (empty($res->origRes["data"])) {
            // No data
            $this->setState($res->http_success, 10, "");
            return;
        }
        if ($res->isJson) {
            if ($res->jsonBody === null) {
                // Couldn't decode JSON
                $this->setState($res->http_success, 11, "");
                return;
            }
            // JSON Data
            if (isset($res->jsonBody->shareMeta)) {
                $this->setOgIfExists($res->jsonBody);
            }

            $this->setState($res->http_success, $this->getCode($res->jsonBody), $this->getMsg($res->jsonBody));
        } elseif ($res->isHtml) {
            if (!$res->hasRehidrate()) {
                // Response doesn't have valid data
                $this->setState($res->http_success, 12, "");
                return;
            }

            $scope = $res->rehidrateState->__DEFAULT_SCOPE__;
            $root = null;

            if (!isset($res->rehidrateState->__DEFAULT_SCOPE__->{"webapp.video-detail"})) {
                // Response doesn't have valid data
                $this->setState($res->http_success, 12, "");
                return;
            }

            $root = $res->rehidrateState->__DEFAULT_SCOPE__->{"webapp.video-detail"};
            $this->setState($res->http_success, $root->statusCode, $root->statusMsg);

            $this->setOgIfExists($root);

        }
    }

    private function setState(bool $http_success, int $proxitokCode, string $proxitokMsg) {
        $this->success = $http_success && $proxitokCode === 0;
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

    private function setOgIfExists(?object $root): void {
        if (isset($root->shareMeta)) {
            $this->og = new \stdClass;
            $this->og->title = $root->shareMeta->title;
            $this->og->description = $root->shareMeta->desc;
        }
    }
}
