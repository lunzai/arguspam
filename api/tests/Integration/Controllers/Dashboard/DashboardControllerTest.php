<?php

namespace Tests\Integration\Controllers\Dashboard;

use App\Models\Org;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Org $org;

    protected function setUp(): void
    {
        parent::setUp();
        $this->org = Org::factory()->create();
        $this->user = User::factory()->create();
        $this->org->users()->attach($this->user->id);
    }

    // -------------------------------------------------------------------------
    // GET /dashboard — index
    // -------------------------------------------------------------------------

    public function test_index_returns_dashboard_data_for_org_member(): void
    {
        $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/dashboard')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [
                'user_count',
                'asset_count',
                'request_count',
                'session_count',
            ]]);
    }

    public function test_index_returns_400_without_org_header(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->getJson('/dashboard')
            ->assertStatus(400);
    }
}
