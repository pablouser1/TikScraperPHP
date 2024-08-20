<?php
namespace TikScraper\Models;

abstract class Base {
    /**
     * Converts item into a JSON string
     * @param bool $pretty_print Use pretty print
     */
    public function toJson(bool $pretty_print = false): string {
        return json_encode(get_object_vars($this), $pretty_print ? JSON_PRETTY_PRINT : 0);
    }
}
