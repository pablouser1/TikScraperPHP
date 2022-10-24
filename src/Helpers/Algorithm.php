<?php
namespace TikScraper\Helpers;

class Algorithm {
    // -- TikTok-focused -- //
    /**
     * Generates verifyFp
     * @todo ADD PROPER VERIFYFP METHOD
     */
    static public function verifyFp(): string {
        return 'verify_e6d8d4a90c859dfc33feefc618ea6c33';
    }

    /**
     * Generates random device ID
     */
    static public function deviceId(): string {
        return strval(self::randomNumber(19));
    }

    // -- Generic -- //
    static public function randomNumber(int $digits = 8): string {
        $characters = '0123456789';
        $randomString = '';
        for ($i = 0; $i < $digits; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }
        return $randomString;
    }

    static public function randomString(int $length = 8): string {
        return bin2hex(random_bytes($length / 2));
    }
}
