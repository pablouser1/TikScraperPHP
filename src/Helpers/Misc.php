<?php
namespace TikScraper\Helpers;

class Misc {
    /**
     * Creates device_id
     */
    public static function makeId(): string {
        $characters = '0123456789';
        $randomString = '';
        $n = 19;
        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }
        return $randomString;
    }

    public static function verify_fp(): string {
        // TODO, ADD PROPER VERIFY_FP METHOD
        return 'verify_e6d8d4a90c859dfc33feefc618ea6c33';
    }

    static public function generateRandomString($length = 10): string {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

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
