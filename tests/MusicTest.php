<?php
const MUSIC_NAME = '6715002916702259202';

$api = initApi();

test('Music Info', function () use ($api) {
    $music = $api->music(MUSIC_NAME);
    expect($music->infoOk())->toBeTrue();
});

test('Music Feed', function () use ($api) {
    $music = $api->music(MUSIC_NAME)->feed();
    expect($music->feedOk())->toBeTrue();
    expect($music->getFeed()->items)->toBeGreaterThan(0);
});
