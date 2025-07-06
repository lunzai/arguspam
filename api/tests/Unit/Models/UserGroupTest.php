<?php

namespace Tests\Unit\Models;

use App\Enums\AssetAccessRole;
use App\Enums\Status;
use App\Models\Asset;
use App\Models\AssetAccessGrant;
use App\Models\Org;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserGroupTest extends TestCase
{
    use RefreshDatabase;

    private UserGroup $userGroup;
    private Org $org;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->org = Org::factory()->create();
        $this->user = User::factory()->create();
        
        $this->userGroup = UserGroup::factory()->create([
            'org_id' => $this->org->id,
            'name' => 'Test User Group',
            'description' => 'A test user group for unit tests',
            'status' => Status::ACTIVE,
        ]);
    }

    public function test_user_group_has_correct_fillable_attributes(): void
    {
        $expectedFillable = [
            'org_id',
            'name',
            'description',
            'status',
        ];

        $this->assertEquals($expectedFillable, $this->userGroup->getFillable());
    }

    public function test_user_group_has_correct_casts(): void
    {
        $casts = $this->userGroup->getCasts();

        $this->assertArrayHasKey('status', $casts);
        $this->assertEquals(Status::class, $casts['status']);
        $this->assertArrayHasKey('created_at', $casts);
        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertArrayHasKey('updated_at', $casts);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertArrayHasKey('deleted_at', $casts);
        $this->assertEquals('datetime', $casts['deleted_at']);
    }

    public function test_user_group_attribute_labels_are_defined(): void
    {
        $expectedLabels = [
            'org_id' => 'Organization',
            'name' => 'Name',
            'description' => 'Description',
            'status' => 'Status',
        ];

        $this->assertEquals($expectedLabels, UserGroup::$attributeLabels);
    }

    public function test_user_group_includable_relationships_are_defined(): void
    {
        $expectedIncludable = [
            'users',
            'assetAccessGrants',
            'org',
            'createdBy',
            'updatedBy',
        ];

        $this->assertEquals($expectedIncludable, UserGroup::$includable);
    }

    public function test_users_relationship(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        // Attach users to the user group
        $this->userGroup->users()->attach([$user1->id, $user2->id]);

        $groupUsers = $this->userGroup->fresh()->users;

        $this->assertCount(2, $groupUsers);
        $this->assertTrue($groupUsers->contains($user1));
        $this->assertTrue($groupUsers->contains($user2));
        $this->assertFalse($groupUsers->contains($user3));

        // Test the relationship type
        $relationship = $this->userGroup->users();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $relationship);
        $this->assertEquals('App\Models\User', $relationship->getRelated()::class);
    }

    public function test_created_by_relationship(): void
    {
        // Test the relationship type
        $relationship = $this->userGroup->createdBy();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relationship);
        $this->assertEquals('App\Models\User', $relationship->getRelated()::class);
        $this->assertEquals('created_by', $relationship->getForeignKeyName());
    }

    public function test_updated_by_relationship(): void
    {
        // Test the relationship type
        $relationship = $this->userGroup->updatedBy();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relationship);
        $this->assertEquals('App\Models\User', $relationship->getRelated()::class);
        $this->assertEquals('updated_by', $relationship->getForeignKeyName());
    }

    public function test_asset_access_grants_relationship(): void
    {
        $asset1 = Asset::factory()->create(['org_id' => $this->org->id]);
        $asset2 = Asset::factory()->create(['org_id' => $this->org->id]);

        $grant1 = AssetAccessGrant::factory()->create([
            'user_group_id' => $this->userGroup->id,
            'asset_id' => $asset1->id,
            'role' => AssetAccessRole::REQUESTER,
        ]);

        $grant2 = AssetAccessGrant::factory()->create([
            'user_group_id' => $this->userGroup->id,
            'asset_id' => $asset2->id,
            'role' => AssetAccessRole::APPROVER,
        ]);

        // Grant for different user group
        $otherUserGroup = UserGroup::factory()->create(['org_id' => $this->org->id]);
        AssetAccessGrant::factory()->create([
            'user_group_id' => $otherUserGroup->id,
            'asset_id' => $asset1->id,
            'role' => AssetAccessRole::REQUESTER,
        ]);

        $userGroupGrants = $this->userGroup->assetAccessGrants;

        $this->assertCount(2, $userGroupGrants);
        $this->assertTrue($userGroupGrants->contains($grant1));
        $this->assertTrue($userGroupGrants->contains($grant2));

        // Test the relationship type
        $relationship = $this->userGroup->assetAccessGrants();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relationship);
        $this->assertEquals('App\Models\AssetAccessGrant', $relationship->getRelated()::class);
    }

    public function test_requester_assets_relationship(): void
    {
        $asset1 = Asset::factory()->create(['org_id' => $this->org->id]);
        $asset2 = Asset::factory()->create(['org_id' => $this->org->id]);

        // Create requester access grant
        AssetAccessGrant::factory()->create([
            'user_group_id' => $this->userGroup->id,
            'asset_id' => $asset1->id,
            'role' => AssetAccessRole::REQUESTER,
        ]);

        // Create approver access grant (should not be included)
        AssetAccessGrant::factory()->create([
            'user_group_id' => $this->userGroup->id,
            'asset_id' => $asset2->id,
            'role' => AssetAccessRole::APPROVER,
        ]);

        $requesterAssets = $this->userGroup->requesterAssets;

        $this->assertCount(1, $requesterAssets);
        $this->assertTrue($requesterAssets->contains($asset1));
        $this->assertFalse($requesterAssets->contains($asset2));

        // Test the relationship type
        $relationship = $this->userGroup->requesterAssets();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $relationship);
        $this->assertEquals('App\Models\Asset', $relationship->getRelated()::class);
    }

    public function test_approver_assets_relationship(): void
    {
        $asset1 = Asset::factory()->create(['org_id' => $this->org->id]);
        $asset2 = Asset::factory()->create(['org_id' => $this->org->id]);

        // Create approver access grant
        AssetAccessGrant::factory()->create([
            'user_group_id' => $this->userGroup->id,
            'asset_id' => $asset1->id,
            'role' => AssetAccessRole::APPROVER,
        ]);

        // Create requester access grant (should not be included)
        AssetAccessGrant::factory()->create([
            'user_group_id' => $this->userGroup->id,
            'asset_id' => $asset2->id,
            'role' => AssetAccessRole::REQUESTER,
        ]);

        $approverAssets = $this->userGroup->approverAssets;

        $this->assertCount(1, $approverAssets);
        $this->assertTrue($approverAssets->contains($asset1));
        $this->assertFalse($approverAssets->contains($asset2));

        // Test the relationship type
        $relationship = $this->userGroup->approverAssets();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $relationship);
        $this->assertEquals('App\Models\Asset', $relationship->getRelated()::class);
    }

    public function test_user_group_uses_correct_traits(): void
    {
        $traits = class_uses_recursive(UserGroup::class);

        $this->assertContains('App\\Traits\\BelongsToOrganization', $traits);
        $this->assertContains('App\\Traits\\HasBlamable', $traits);
        $this->assertContains('Illuminate\\Database\\Eloquent\\Factories\\HasFactory', $traits);
        $this->assertContains('App\\Traits\\HasStatus', $traits);
    }

    public function test_user_group_extends_base_model(): void
    {
        $this->assertInstanceOf(\App\Models\Model::class, $this->userGroup);
    }

    public function test_user_group_creation_with_all_attributes(): void
    {
        $org = Org::factory()->create();
        $creator = User::factory()->create();

        $userGroup = UserGroup::factory()->create([
            'org_id' => $org->id,
            'name' => 'Custom User Group',
            'description' => 'A custom user group with all attributes',
            'status' => Status::INACTIVE,
            'created_by' => $creator->id,
            'updated_by' => $creator->id,
        ]);

        $this->assertDatabaseHas('user_groups', [
            'org_id' => $org->id,
            'name' => 'Custom User Group',
            'description' => 'A custom user group with all attributes',
            'status' => Status::INACTIVE->value,
        ]);

        // Test that status is cast correctly
        $this->assertEquals(Status::INACTIVE, $userGroup->status);
        $this->assertInstanceOf(Status::class, $userGroup->status);
    }

    public function test_user_group_belongs_to_organization(): void
    {
        // Test through BelongsToOrganization trait
        $this->assertEquals($this->org->id, $this->userGroup->org_id);
        
        // Test org relationship (from BelongsToOrganization trait)
        $this->assertInstanceOf(Org::class, $this->userGroup->org);
        $this->assertEquals($this->org->id, $this->userGroup->org->id);
    }

    public function test_user_group_can_be_attached_to_multiple_users(): void
    {
        $users = User::factory()->count(5)->create();

        // Attach all users to the user group
        $this->userGroup->users()->attach($users->pluck('id'));

        $this->assertCount(5, $this->userGroup->fresh()->users);

        // Verify each user has the user group
        foreach ($users as $user) {
            $this->assertTrue($user->fresh()->userGroups->contains($this->userGroup));
        }
    }

    public function test_user_group_asset_access_grants_filtering(): void
    {
        $asset1 = Asset::factory()->create(['org_id' => $this->org->id]);
        $asset2 = Asset::factory()->create(['org_id' => $this->org->id]);
        $asset3 = Asset::factory()->create(['org_id' => $this->org->id]);

        // Create different types of access grants
        AssetAccessGrant::factory()->create([
            'user_group_id' => $this->userGroup->id,
            'asset_id' => $asset1->id,
            'role' => AssetAccessRole::REQUESTER,
        ]);

        AssetAccessGrant::factory()->create([
            'user_group_id' => $this->userGroup->id,
            'asset_id' => $asset2->id,
            'role' => AssetAccessRole::APPROVER,
        ]);

        AssetAccessGrant::factory()->create([
            'user_group_id' => $this->userGroup->id,
            'asset_id' => $asset3->id,
            'role' => AssetAccessRole::REQUESTER,
        ]);

        $requesterAssets = $this->userGroup->requesterAssets;
        $approverAssets = $this->userGroup->approverAssets;

        // Verify filtering works correctly
        $this->assertCount(2, $requesterAssets); // asset1 and asset3
        $this->assertCount(1, $approverAssets);  // asset2 only

        $this->assertTrue($requesterAssets->contains($asset1));
        $this->assertTrue($requesterAssets->contains($asset3));
        $this->assertFalse($requesterAssets->contains($asset2));

        $this->assertTrue($approverAssets->contains($asset2));
        $this->assertFalse($approverAssets->contains($asset1));
        $this->assertFalse($approverAssets->contains($asset3));
    }

    public function test_user_group_relationships_can_be_detached(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create(['org_id' => $this->org->id]);

        // Attach user
        $this->userGroup->users()->attach($user->id);
        $this->assertCount(1, $this->userGroup->fresh()->users);

        // Create asset access grant
        $grant = AssetAccessGrant::factory()->create([
            'user_group_id' => $this->userGroup->id,
            'asset_id' => $asset->id,
            'role' => AssetAccessRole::REQUESTER,
        ]);
        $this->assertCount(1, $this->userGroup->fresh()->assetAccessGrants);

        // Detach user
        $this->userGroup->users()->detach($user->id);
        $this->assertCount(0, $this->userGroup->fresh()->users);

        // Delete asset access grant
        $grant->delete();
        $this->assertCount(0, $this->userGroup->fresh()->assetAccessGrants);
    }

    public function test_status_enum_functionality(): void
    {
        // Test active status
        $activeGroup = UserGroup::factory()->create([
            'org_id' => $this->org->id,
            'status' => Status::ACTIVE,
        ]);

        // Test inactive status
        $inactiveGroup = UserGroup::factory()->create([
            'org_id' => $this->org->id,
            'status' => Status::INACTIVE,
        ]);

        $this->assertEquals(Status::ACTIVE, $activeGroup->status);
        $this->assertEquals(Status::INACTIVE, $inactiveGroup->status);
        $this->assertInstanceOf(Status::class, $activeGroup->status);
        $this->assertInstanceOf(Status::class, $inactiveGroup->status);
    }
}