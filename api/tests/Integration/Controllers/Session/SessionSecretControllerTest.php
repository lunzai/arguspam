<?php

namespace Tests\Integration\Controllers\Session;

use App\Enums\AssetAccountType;
use App\Enums\SessionStatus;
use App\Models\Asset;
use App\Models\AssetAccount;
use App\Models\Org;
use App\Models\Session;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SessionSecretControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Org $org;
    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        $this->org = Org::factory()->create();
        $this->user = User::factory()->create();
        $this->org->users()->attach($this->user->id);
        $this->asset = Asset::factory()->create(['org_id' => $this->org->id]);
    }

    private function startedSession(int $requesterId): Session
    {
        $account = AssetAccount::factory()->create([
            'asset_id' => $this->asset->id,
            'type' => AssetAccountType::JIT,
            'is_active' => true,
        ]);

        return Session::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $requesterId,
            'asset_account_id' => $account->id,
            'status' => SessionStatus::STARTED,
            'scheduled_start_datetime' => now()->subHour(),
            'scheduled_end_datetime' => now()->addHour(),
            'start_datetime' => now()->subMinutes(5),
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /sessions/{session}/secret — show
    // -------------------------------------------------------------------------

    public function test_show_returns_credentials_for_requester(): void
    {
        $this->giveUserPermission($this->user, 'session:retrievesecret');
        $session = $this->startedSession($this->user->id);

        $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/sessions/{$session->id}/secret")
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['host', 'port', 'username', 'password']]);
    }

    public function test_show_returns_403_for_non_requester(): void
    {
        $this->giveUserPermission($this->user, 'session:retrievesecret');
        $otherUser = User::factory()->create();
        $this->org->users()->attach($otherUser->id);
        $session = $this->startedSession($otherUser->id);

        $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/sessions/{$session->id}/secret")
            ->assertStatus(403);
    }

    public function test_show_returns_422_for_non_started_session(): void
    {
        $this->giveUserPermission($this->user, 'session:retrievesecret');
        $session = Session::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->user->id,
            'status' => SessionStatus::SCHEDULED,
            'scheduled_start_datetime' => now()->subMinute(),
            'scheduled_end_datetime' => now()->addHour(),
        ]);

        $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/sessions/{$session->id}/secret")
            ->assertStatus(422);
    }

    public function test_show_returns_403_without_permission(): void
    {
        $session = $this->startedSession($this->user->id);

        $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/sessions/{$session->id}/secret")
            ->assertStatus(403);
    }
}
