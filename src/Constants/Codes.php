<?php
namespace TikScraper\Constants;

enum Codes: int {
    case OK = 0;
    case UNKNOWN = 1;
    case NETWORK_ERROR = 2;
    case EMPTY_RESPONSE = 3;
    case JSON_DECODE_ERROR = 4;
    case STATE_DECODE_ERROR = 5;
    // TikTok's verify issue
    case VERIFY = 10000;
}
