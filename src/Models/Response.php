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
    public ?object $sigiState = null;
    public ?object $rehidrateState = null;
    public bool $hasSigi = false;
    public bool $hasRehidrate = false;

    function __construct(?ResponseInterface $origRes) {
        $code = $origRes === null ? 503 : $origRes->getStatusCode();
        $this->http_success = $code >= 200 && $code < 400;
        $this->code = $code;
        $this->origRes = $origRes;
        if ($origRes !== null) {
            $hasContentType = $this->origRes->hasHeader("Content-Type");
            $this->isJson = $hasContentType && strpos($this->origRes->getHeaderLine("Content-Type"), "application/json") !== false;
            $this->isHtml = $hasContentType && strpos($this->origRes->getHeaderLine("Content-Type"), "text/html") !== false;
            
            $this->jsonBody = $this->isJson ? json_decode($this->origRes->getBody()) : null;
            
            if ($this->isHtml) {
                $dom = Misc::getDoc($this->origRes->getBody());

                // We make sure is not a challenge
                if ($dom->getElementById("wci") !== null && $dom->getElementById("cs") !== null) {
                    $this->isChallenge = true;
                } else {
                    // Try to get SIGI State
                    if ($dom->getElementById("SIGI_STATE") !== null) {
                        $this->sigiState = Misc::extractSigi($this->origRes->getBody(), $dom);
                        if ($this->sigiState !== null) {
                            $this->hasSigi = true;
                        }
                    }

                    // Try to get Rehydration
                    if ($dom->getElementById("__UNIVERSAL_DATA_FOR_REHYDRATION__") !== null) {
                        $this->rehidrateState = Misc::extractHydra($this->origRes->getBody(), $dom);
                        if ($this->rehidrateState !== null) {
                            $this->hasRehidrate = true;
                        }
                    }
                } 
            }
        }
    }
}
