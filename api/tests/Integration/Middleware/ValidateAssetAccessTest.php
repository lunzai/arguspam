<?php

namespace Tests\Integration\Middleware;

use App\Enums\AssetAccessRole;
use App\Http\Middleware\ValidateAssetAccess;
use App\Models\Asset;
use App\Models\AssetAccessGrant;
use App\Models\Org;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ValidateAssetAccessTest extends TestCase
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

        Route::middleware(['auth:sanctum', SubstituteBindings::class, ValidateAssetAccess::class])
            ->get('/test-asset-access/{asset}', fn (Asset $asset) => response()->json(['ok' => true]));
    }

    public function test_user_with_direct_access_grant_can_access(): void
    {
        AssetAccessGrant::factory()->create([
            'asset_id' => $this->asset->id,
            'user_id' => $this->user->id,
            'role' => AssetAccessRole::REQUESTER,
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/test-asset-access/{$this->asset->id}")
            ->assertStatus(200);
    }

    public function test_user_without_access_grant_is_denied(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->getJson("/test-asset-access/{$this->asset->id}")
            ->assertStatus(403)
            ->assertJsonFragment(['error' => 'You do not have the required access to this asset']);
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $this->getJson("/test-asset-access/{$this->asset->id}")
            ->assertStatus(401);
    }

    public function test_invalid_asset_returns_400(): void
    {
        // Pass a non-existent ID — route model binding will 404 before reaching middleware
        $this->actingAs($this->user, 'sanctum')
            ->getJson('/test-asset-access/99999')
            ->assertStatus(404);
    }

    public function test_user_with_group_access_grant_can_access(): void
    {
        $userGroup = UserGroup::factory()->create(['org_id' => $this->org->id]);
        $userGroup->users()->attach($this->user->id);

        AssetAccessGrant::factory()->create([
            'asset_id' => $this->asset->id,
            'user_group_id' => $userGroup->id,
            'user_id' => null,
            'role' => AssetAccessRole::REQUESTER,
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/test-asset-access/{$this->asset->id}")
            ->assertStatus(200);
    }
}
