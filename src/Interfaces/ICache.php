<?php
namespace TikScraper\Interfaces;

interface ICache {
    public function get(string $cache_key): ?object;
    public function exists(string $cache_key): bool;
    public function set(string $cache_key, string $data, int $timeout = 3600);
}
