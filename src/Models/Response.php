<?php
namespace TikScraper\Models;
use Psr\Http\Message\ResponseInterface;
use TikScraper\Helpers\Misc;

class Response {
    public bool $http_success;
    public int $code;
    public ?ResponseInterface $origRes;

    public bool $isJson = false;
    public bool $isHtml = false;

    public ?object $jsonBody = null;

    // -- HTML ONLY -- //
    public bool $isChallenge = false;
    public ?object $rehidrateState = null;

    function __construct(?ResponseInterface $origRes) {
        $code = $origRes === null ? 503 : $origRes->getStatusCode();
        $this->http_success = $code >= 200 && $code < 400;
        $this->code = $code;
        $this->origRes = $origRes;
        if ($origRes !== null) {
            $hasContentType = $this->origRes->hasHeader("Content-Type");
            $this->isJson = $hasContentType && strpos($this->origRes->getHeaderLine("Content-Type"), "application/json") !== false;
            $this->isHtml = $hasContentType && strpos($this->origRes->getHeaderLine("Content-Type"), "text/html") !== false;

            if ($this->isJson) {
                $this->jsonBody = json_decode($this->origRes->getBody());
            } else if ($this->isHtml) {
                $dom = Misc::getDoc($this->origRes->getBody());

                // We make sure is not a challenge
                if ($dom->getElementById("wci") !== null && $dom->getElementById("cs") !== null) {
                    $this->isChallenge = true;
                // Try to get Rehydration
                } else if ($dom->getElementById("__UNIVERSAL_DATA_FOR_REHYDRATION__") !== null) {
                    $this->rehidrateState = Misc::extractHydra($this->origRes->getBody(), $dom);
                }
            }
        }
    }

    public function hasRehidrate(): bool {
        return $this->rehidrateState !== null;
    }
}
