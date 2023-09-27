<?php
namespace TikScraper\Helpers;

class Algorithm {
    // -- TikTok-focused -- //
    /**
     * Generates random device ID
     */
    static public function deviceId(): string {
        return strval(self::randomNumber(19));
    }

    /**
     * TikTok's new JS challenge that may show up
     */
    static public function challenge(string $type, string $key): string {
        $c = json_decode(base64_decode($key)); // var c
        $prefix = base64_decode($c->v->a); // var prefix

        $expect = bin2hex(base64_decode($c->v->c)); // var expect
        
        $i = 0;
        $result = '';
        while ($i < 1000000 && $result === '') {
            $hashFinal = '';
            $res = [];
            // Build sha256 with $prefix and current $i
            $hash = hash_init("sha256");
            hash_update($hash, $prefix);
            hash_update($hash, $i);
            $hashResult = hash_final($hash, true);
            $strArr = str_split($hashResult);

            // Do some byte shifting required by the challenge
            foreach ($strArr as $el) {
                $tmp = [];
                $chr = ord($el); // var v
                $tmpNum = $chr < 0 ? $chr + 256 : $chr; // var c
                array_push($tmp, dechex(self::uRShift($tmpNum, 4)));
                array_push($tmp, dechex($tmpNum & 0xf));

                array_push($res, implode($tmp));
            }
            
            $hashFinal = implode($res);

            // Check if the challenge is completed
            if ($expect === $hashFinal) {
                $c->d = base64_encode(strval($i));
                // We use unescaped slashes to get the samme result as the original JS version
                $result = base64_encode(json_encode($c, JSON_UNESCAPED_SLASHES));
            }
            $i++;
        }

        return $result;
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

    static public function uRShift(int $a, int $b) {
        if($b == 0) return $a;
        return ($a >> $b) & ~(1<<(8*PHP_INT_SIZE-1)>>($b-1));
    }
}
