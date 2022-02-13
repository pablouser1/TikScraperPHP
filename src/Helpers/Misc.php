<?php
namespace TikScraper\Helpers;

class Misc {
    public static function makeId(): string {
        $characters = '0123456789';
        $randomString = '';
        $n = 16;
        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        return "68" . $randomString;
    }

    /**
     * Verify Fingerprint, implementation from drawrowfly/tiktok-scraper adapted to PHP
     * @link https://github.com/drawrowfly/tiktok-scraper/blob/5224f5cdfc3842a99b77b382249b960d2c87791c/src/helpers/Random.ts#L19
     */
    public static function verify_fp(): string {
        $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $charlen = strlen($chars);
        $time = base64_encode(str_replace('.', '', microtime(true)));
        $arr = mb_str_split(str_repeat('0', 36));
        $arr[8] = '_';
        $arr[13] = '_';
        $arr[18] = '_';
        $arr[23] = '_';
        $arr[14] = '4';
        $new_arr = array_map(function ($x) use ($chars, $charlen) {
            $rand_num = mt_rand() / mt_getrandmax() * $charlen;
            $index = (int) floor($rand_num);
            return $x === '0' ? substr($chars, $index, 1) : $x;
        }, $arr);
        $str = implode('', $new_arr);
        return 'verify_' . strtolower($time) . '_' . $str;
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

    public static function string_between(string $string, string $start, string $end): string {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini === 0) {
            return '';
        }

        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    public static function normalize($string) {
        $string = preg_replace("/([^a-z0-9])/", "-", strtolower($string));
        $string = preg_replace("/(\s+)/", "-", strtolower($string));
        $string = preg_replace("/([-]+){2,}/", "-", strtolower($string));
        return $string;
    }
}
