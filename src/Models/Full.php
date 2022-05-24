<?php
namespace TikScraper\Models;

class Full extends Base {
    public Info $info;
    public Feed $feed;

    function __construct(Info $info, Feed $feed) {
        $this->info = $info;
        $this->feed = $feed;
    }
}
