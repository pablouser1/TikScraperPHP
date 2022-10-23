<?php
const VIDEO_ID = '7154026953975090437';

$api = initApi();

test('Video Info', function () use ($api) {
    $vid = $api->video(VIDEO_ID)->feed();
    expect($vid->ok())->toBeTrue();
});
