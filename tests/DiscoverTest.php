<?php
$api = initApi();

test('Discover', function () use ($api) {
    $discover = $api->discover();
    expect($discover->meta->success)->toBeTrue();
});
