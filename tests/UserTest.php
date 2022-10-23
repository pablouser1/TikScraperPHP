<?php
const USERNAME = 'willsmith';

$api = initApi();

test('User Info', function () use ($api) {
    $user = $api->user(USERNAME);
    expect($user->infoOk())->toBeTrue();
});

test('User Feed', function () use ($api) {
    $user = $api->user(USERNAME)->feed();
    expect($user->feedOk())->toBeTrue();
    expect($user->getFeed()->items)->toBeGreaterThan(0);
});

// Checks if sending an invalid username actually does send an error
test('Invalid User', function () use ($api) {
    $user = $api->user(randStr());
    expect($user->infoOk())->toBeFalse();
    $meta = $user->error();
    expect($meta->http_code)->toBe(404);
});
