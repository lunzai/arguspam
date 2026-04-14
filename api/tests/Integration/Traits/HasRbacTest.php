<?php

namespace Tests\Integration\Traits;

use App\Models\Asset;
use App\Models\Org;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class HasRbacTest extends TestCase
{
    use RefreshDatabase;

    private Org $org;
    private User $user;
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

    public function test_can_access_asset_returns_false_when_user_has_no_grants(): void
    {
        $result = $this->user->canAccessAsset($this->user, $this->asset);

        $this->assertFalse($result);
    }

    public function test_can_request_returns_false_when_no_grants(): void
    {
        $result = $this->user->canRequest($this->asset);

        $this->assertFalse($result);
    }

    public function test_can_approve_returns_false_when_no_grants(): void
    {
        $result = $this->user->canApprove($this->asset);

        $this->assertFalse($result);
    }
}
