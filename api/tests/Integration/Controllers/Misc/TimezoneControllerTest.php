<?php

namespace Tests\Integration\Controllers\Misc;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimezoneControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // GET /utils/timezones — index
    // -------------------------------------------------------------------------

    public function test_index_returns_200_with_timezone_list(): void
    {
        $response = $this->getJson('/utils/timezones')
            ->assertStatus(200)
            ->assertJsonStructure(['data']);

        $this->assertNotEmpty($response->json('data'));
    }

    public function test_index_includes_known_timezones(): void
    {
        $response = $this->getJson('/utils/timezones')
            ->assertStatus(200);

        $timezones = $response->json('data');
        $this->assertContains('UTC', $timezones);
        $this->assertContains('America/New_York', $timezones);
    }
}
