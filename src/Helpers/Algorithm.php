<?php
namespace TikScraper\Helpers;

class Algorithm {
    static public function deviceId(): string {
        $characters = '0123456789';
        $randomString = '';
        $n = 19;
        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }
        return $randomString;
    }

    /**
     * Generates verifyFp strings
     * @todo ADD PROPER VERIFYFP METHOD
     */
    static public function verifyFp(): string {
        return 'verify_e6d8d4a90c859dfc33feefc618ea6c33';
    }

    static public function randomString(int $length = 8): string {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
