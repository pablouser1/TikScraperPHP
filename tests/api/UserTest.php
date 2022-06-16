<?php
require_once __DIR__ . '/../DefaultTest.php';

final class UserTest extends DefaultTest {
    /**
     * TODO, COMPLETE TESTING
     */
    public function testWeb(): void {
        $user = $this->api->user('willsmith');
        $info = $user->getInfo();

        // Test meta
        $this->assertTrue($info->meta->success, 'Request was not successful!');
    }
}
