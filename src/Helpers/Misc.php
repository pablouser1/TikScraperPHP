<?php
namespace TikScraper\Helpers;

use Psr\Http\Message\StreamInterface;

class Misc {
    public static function getDoc(StreamInterface $body): ?\DOMDocument {
        // Disallow empty strings
        if ($body->getSize() > 0) {
            $dom = new \DomDocument();
            @$dom->loadHTML($body);
            return $dom;
        }
        return null;
    }
    /**
     * Get JSON data from HTML string
     */
    public static function extractHydra(StreamInterface $body, ?\DOMDocument $dom = null): ?object {
        return self::__extractByTagName("__UNIVERSAL_DATA_FOR_REHYDRATION__", $body, $dom);
    }

    private static function __extractByTagName(string $tagName, StreamInterface $body, ?\DOMDocument $dom = null): ?object {
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
