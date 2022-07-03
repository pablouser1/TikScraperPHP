<?php
namespace TikScraper\Models;

abstract class Base {
    /**
     * Convert Item to a JSON string
     * @param bool $pretty_print Use whitespace in returned data to format it
     */
    public function toJson(bool $pretty_print = false): string {
        return json_encode(get_object_vars($this), $pretty_print ? JSON_PRETTY_PRINT : 0);
    }
}
