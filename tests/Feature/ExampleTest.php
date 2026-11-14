<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_application_returns_the_react_mount_point(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('id="app"', false);
    }
}
