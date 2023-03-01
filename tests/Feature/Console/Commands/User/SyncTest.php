<?php

namespace Tests\Feature\Console\Commands\User;

use Tests\TestCase;

class SyncTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
