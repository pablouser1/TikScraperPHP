<?php
namespace TikScraper\Models;
use TikScraper\Helpers\Misc;

class Response {
    public bool $http_success;
    public int $code;
    public array $origRes;

    public bool $isJson = false;
    public bool $isHtml = false;

    public ?object $jsonBody = null;

    // -- HTML ONLY -- //
    public ?object $rehidrateState = null;

    function __construct(array $res) {
        $this->code = $res["code"];
        $this->http_success = $this->code >= 200 && $this->code <= 399;

        $this->isHtml = $res["type"] === "html";
        $this->isJson = $res["type"] === "json";

        if ($this->isJson) {
            // Converts body into an object
            // TODO: Maybe a better way to do this?
            $this->jsonBody = json_decode(json_encode($res["data"]));
        } elseif ($this->isHtml) {
            $dom = Misc::getDoc($res["data"]);
            if ($dom->getElementById("__UNIVERSAL_DATA_FOR_REHYDRATION__") !== null) {
                $this->rehidrateState = Misc::extractHydra($res["data"], $dom);
            }
        }

        $this->origRes = $res;
    }

    public function hasRehidrate(): bool {
        return $this->rehidrateState !== null;
    }
}
