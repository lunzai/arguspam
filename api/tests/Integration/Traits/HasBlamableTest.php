<?php

namespace Tests\Integration\Traits;

use App\Models\Asset;
use App\Models\Org;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class HasBlamableTest extends TestCase
{
    use RefreshDatabase;

    private Org $org;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        $this->org = Org::factory()->create();
        $this->user = User::factory()->create();
        $this->org->users()->attach($this->user->id);
    }

    public function test_created_by_relationship_returns_user(): void
    {
        // Authenticate so Auth::id() is set when bootHasBlamable fires
        $this->actingAs($this->user, 'sanctum');
        $asset = Asset::factory()->create(['org_id' => $this->org->id]);

        $createdBy = $asset->createdBy;

        $this->assertInstanceOf(User::class, $createdBy);
        $this->assertEquals($this->user->id, $createdBy->id);
    }

    public function test_updated_by_relationship_returns_user(): void
    {
        $this->actingAs($this->user, 'sanctum');
        $asset = Asset::factory()->create(['org_id' => $this->org->id]);

        $updatedBy = $asset->updatedBy;

        $this->assertInstanceOf(User::class, $updatedBy);
        $this->assertEquals($this->user->id, $updatedBy->id);
    }
}
