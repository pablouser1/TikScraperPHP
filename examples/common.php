<?php
use TikScraper\Models\Meta;

function printError(Meta $meta) {
    echo "Error processing request!\n";
    echo "HTTP " . $meta->httpCode . "\n";
    echo "Code " . $meta->proxitokCode . " (" . $meta->proxitokMsg . ")";
}
