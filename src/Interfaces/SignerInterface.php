<?php
namespace TikScraper\Interfaces;

interface SignerInterface {
    public function __construct(array $config = []);
    public function run(string $unsigned_url): ?object;
}
