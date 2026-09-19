<?php

namespace Tests\Feature;

use Tests\TestCase;

class WelcomeTest extends TestCase
{
    public function test_homepage_shows_hello_world(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Hola mundo');
    }
}
