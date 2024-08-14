<?php
namespace TikScraper\Constants;
use TikScraper\Models\Response;

final class Responses {
    public static function ok(): Response {
        return self::__buildResponse(200, 0);
    }

    public static function badChallenge(): Response {
        return self::__buildResponse(503, 13);
    }

    public static function badSign(): Response {
        return self::__buildResponse(503, 20);
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
