<?php
namespace TikScraper\Constants;

/**
 * List of codes from ProxiTok's and TikTok's internal codes
 */
enum Codes: int {
    /** Request went ok */
    case OK = 0;
    /** An unknown error happened */
    case UNKNOWN = 1;
    /** There was a network error (timeouts, invalid proxy...) */
    case NETWORK_ERROR = 2;
    /** There was a response, but the body was empty */
    case EMPTY_RESPONSE = 3;
    /** JSON data could not be decoded */
    case JSON_DECODE_ERROR = 4;
    /** UNIVERSAL HIDRATION data could not be decoded */
    case STATE_DECODE_ERROR = 5;
    /** TikTok is asking for a captcha */
    case VERIFY = 10000;
}
