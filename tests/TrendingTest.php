<?php
$api = initApi();

test('Trending', function () use ($api) {
    $trending = $api->trending()->feed();
    expect($trending->feedOk())->toBeTrue();
});
