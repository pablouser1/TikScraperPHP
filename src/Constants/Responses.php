<?php
namespace TikScraper\Constants;
use TikScraper\Models\Response;

/**
 * Predefined responses used mainly for caching
 */
final class Responses {
    /**
     * Spoof an OK response from TikTok
     * @return \TikScraper\Models\Response
     */
    public static function ok(): Response {
        return self::__buildResponse(200, 0);
    }

    private static function __buildResponse(int $code, int $statusCode, string $contentType = 'json'): Response {
        return new Response([
            "type" => $contentType,
            "code" => $code,
            "success" => $code >= 200 && $code < 400,
            "data" => ["statusCode" => $statusCode],
            "headers" => []
        ]);
    }
}
