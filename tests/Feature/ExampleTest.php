<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_returns_a_successful_response(): void
    {
        $this->get('/')->assertStatus(200);
    }

    public function test_every_public_route_renders(): void
    {
        $this->get('/clients')->assertStatus(200);
        $this->get('/contact')->assertStatus(200);
    }
}
