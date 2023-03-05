<?php

namespace Tests\Feature\Console\Commands\Saldo;

use Tests\TestCase;

class RecalculateTest extends TestCase
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
