<?php
namespace TikScraper\Models;

class Base {
    public function ToJson(bool $pretty_print = false): string {
        return json_encode(get_object_vars($this), $pretty_print ? JSON_PRETTY_PRINT : 0);
    }
}
