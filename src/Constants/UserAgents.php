<?php
namespace TikScraper\Constants;

/**
 * List with predefined user agents, to be used for Guzzle
 * @deprecated User agents are now always picked from Selenium
 */
abstract class UserAgents {
    const DEFAULT = "Mozilla/5.0 (Windows NT 10.0; rv:127.0) Gecko/20100101 Firefox/127.0";
}
