<?php
const TAG_NAME = 'funny';

$api = initApi();

test('Hashtag Info', function () use ($api) {
    $tag = $api->hashtag(TAG_NAME);
    expect($tag->infoOk())->toBeTrue();
});

test('Hashtag Feed', function () use ($api) {
    $tag = $api->hashtag(TAG_NAME)->feed();
    expect($tag->feedOk())->toBeTrue();
    expect($tag->getFeed()->items)->toBeGreaterThan(0);
});
