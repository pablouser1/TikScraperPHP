<?php
namespace TikScraper\Helpers;

class Misc {
    /**
     * Builds `DomDocument` from php-xml from a string
     * @param string $body HTML body as a string
     * @return \DomDocument|null Document if successful, null if string is empty
     */
    public static function getDoc(string $body): ?\DOMDocument {
        // Disallow empty strings
        if ($body !== "") {
            $dom = new \DOMDocument();
            @$dom->loadHTML($body);
            return $dom;
        }
        return null;
    }
    /**
     * Get `__UNIVERSAL_DATA_FOR_REHYDRATION__` from `DOMDocument` or string if `$dom` is null
     * @param string $body HTML body as a string
     * @param \DOMDocument|null HTML body as `DOMDocument`
     */
    public static function extractHydra(string $body, ?\DOMDocument $dom = null): ?object {
        return self::__extractByTagName("__UNIVERSAL_DATA_FOR_REHYDRATION__", $body, $dom);
    }

    /**
     * Get JSON data from tag inside document
     * @param string $tagName HTML element id
     * @param string $body HTML body as a string
     * @param \DOMDocument|null $dom HTML body as `DOMDocument`
     * @return object|null Object with JSON data if successful, null if not
     */
    private static function __extractByTagName(string $tagName, string $body, ?\DOMDocument $dom = null): ?object {
        // Disallow empty strings
        $dom = $dom ?? self::getDoc($body);
        if ($dom !== null) {
            $script = $dom->getElementById($tagName);
            if ($script !== null) {
                return json_decode($script->textContent);
            }
        }
        return null;
    }
}
