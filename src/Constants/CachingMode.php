<?php
namespace TikScraper\Constants;

/**
 * Caching mode to use for Items
 */
enum CachingMode {
    /** Do not cache anything */
    case NONE;
    /** Cache only the feed, ignore info */
    case FEED_ONLY;
    /** Cache everything */
    case FULL;
}
