<?php
namespace TikScraper\Helpers;

class Misc {
    /**
     * Get Sigi State from HTML string
     */
    public static function extractSigi(string $doc): ?object {
        // Disallow empty strings
        if ($doc !== "") {
            $dom = new \DomDocument();
            @$dom->loadHTML($doc);
            $script = $dom->getElementById('SIGI_STATE');
            if ($script) {
                return json_decode($script->textContent);
            }
        }
        return null;
    }
}
