<?php
namespace TikScraper\Interfaces;

interface CacheInterface {
    public function get(string $cache_key): ?object;
    public function exists(string $cache_key): bool;
    public function set(string $cache_key, string $data, $timeout = 3600);
}
