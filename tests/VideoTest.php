<?php
const VIDEO_ID = '7078030558684564779';
const VIDEO_INVALID = '24938567439573495743985789435435';

$api = initApi();

test('Video Info', function () use ($api) {
    $vid = $api->video(VIDEO_ID)->feed();
    expect($vid->ok())->toBeTrue();
});

test('Invalid video', function () use ($api) {
    $vid = $api->video(VIDEO_INVALID)->feed();
    expect($vid->ok())->toBeFalse();
    $meta = $vid->error();
    expect($meta->proxitokCode)->not()->toBe(0);
});
