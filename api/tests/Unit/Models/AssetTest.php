<?php

namespace Tests\Unit\Models;

use App\Enums\AssetAccessRole;
use App\Enums\Dbms;
use App\Enums\Status;
use App\Models\Asset;
use App\Models\AssetAccessGrant;
use App\Models\AssetAccount;
use App\Models\Org;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetTest extends TestCase
{
    use RefreshDatabase;

    private Asset $asset;
    private Org $org;

    protected function setUp(): void
    {
        parent::setUp();

        $this->org = Org::factory()->create();
        $this->asset = Asset::factory()->create([
            'org_id' => $this->org->id,
        ]);
    }

    public function test_asset_has_correct_fillable_attributes(): void
    {
        $expectedFillable = [
            'org_id',
            'name',
            'description',
            'status',
            'host',
            'port',
            'dbms',
        ];

        $this->assertEquals($expectedFillable, $this->asset->getFillable());
    }

    public function test_asset_has_correct_casts(): void
    {
        $casts = $this->asset->getCasts();

        $this->assertArrayHasKey('created_at', $casts);
        $this->assertArrayHasKey('updated_at', $casts);
        $this->assertArrayHasKey('deleted_at', $casts);
        $this->assertArrayHasKey('dbms', $casts);
        $this->assertArrayHasKey('status', $casts);
        $this->assertEquals(Dbms::class, $casts['dbms']);
        $this->assertEquals(Status::class, $casts['status']);
    }

    public function test_asset_belongs_to_organization(): void
    {
        $this->assertInstanceOf(Org::class, $this->asset->org);
        $this->assertEquals($this->org->id, $this->asset->org->id);
    }

    public function test_asset_has_many_accounts(): void
    {
        $account1 = AssetAccount::factory()->create(['asset_id' => $this->asset->id]);
        $account2 = AssetAccount::factory()->create(['asset_id' => $this->asset->id]);
        AssetAccount::factory()->create(); // Different asset

        $accounts = $this->asset->accounts;

        $this->assertCount(2, $accounts);
        $this->assertTrue($accounts->contains($account1));
        $this->assertTrue($accounts->contains($account2));
    }

    public function test_asset_has_many_access_grants(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $grant1 = AssetAccessGrant::factory()->create([
            'asset_id' => $this->asset->id,
            'user_id' => $user1->id,
            'role' => AssetAccessRole::REQUESTER,
        ]);
        $grant2 = AssetAccessGrant::factory()->create([
            'asset_id' => $this->asset->id,
            'user_id' => $user2->id,
            'role' => AssetAccessRole::APPROVER,
        ]);
        AssetAccessGrant::factory()->create([
            'user_id' => User::factory()->create()->id,
            'role' => AssetAccessRole::REQUESTER,
        ]); // Different asset

        $grants = $this->asset->accessGrants;

        $this->assertCount(2, $grants);
        $this->assertTrue($grants->contains($grant1));
        $this->assertTrue($grants->contains($grant2));
    }

    public function test_asset_has_many_requests(): void
    {
        // Test that the relationship method exists and returns a HasMany relationship
        $relationship = $this->asset->requests();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relationship);
        $this->assertEquals('asset_id', $relationship->getForeignKeyName());
    }

    public function test_asset_has_many_sessions(): void
    {
        // Test that the relationship method exists and returns a HasMany relationship
        $relationship = $this->asset->sessions();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relationship);
        $this->assertEquals('asset_id', $relationship->getForeignKeyName());
    }

    public function test_asset_belongs_to_many_users(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        // Create access grants for users 1 and 2
        AssetAccessGrant::factory()->create([
            'asset_id' => $this->asset->id,
            'user_id' => $user1->id,
            'role' => AssetAccessRole::REQUESTER,
        ]);

        AssetAccessGrant::factory()->create([
            'asset_id' => $this->asset->id,
            'user_id' => $user2->id,
            'role' => AssetAccessRole::APPROVER,
        ]);

        $users = $this->asset->users;

        $this->assertCount(2, $users);
        $this->assertTrue($users->contains($user1));
        $this->assertTrue($users->contains($user2));
        $this->assertFalse($users->contains($user3));
    }

    public function test_asset_belongs_to_many_user_groups(): void
    {
        $group1 = UserGroup::factory()->create();
        $group2 = UserGroup::factory()->create();
        $group3 = UserGroup::factory()->create();

        // Create access grants for groups 1 and 2
        AssetAccessGrant::factory()->create([
            'asset_id' => $this->asset->id,
            'user_group_id' => $group1->id,
            'role' => AssetAccessRole::REQUESTER,
        ]);

        AssetAccessGrant::factory()->create([
            'asset_id' => $this->asset->id,
            'user_group_id' => $group2->id,
            'role' => AssetAccessRole::APPROVER,
        ]);

        $groups = $this->asset->userGroups;

        $this->assertCount(2, $groups);
        $this->assertTrue($groups->contains($group1));
        $this->assertTrue($groups->contains($group2));
        $this->assertFalse($groups->contains($group3));
    }

    public function test_asset_approver_user_groups_filters_by_role(): void
    {
        $approverGroup = UserGroup::factory()->create();
        $requesterGroup = UserGroup::factory()->create();

        AssetAccessGrant::factory()->create([
            'asset_id' => $this->asset->id,
            'user_group_id' => $approverGroup->id,
            'role' => AssetAccessRole::APPROVER,
        ]);

        AssetAccessGrant::factory()->create([
            'asset_id' => $this->asset->id,
            'user_group_id' => $requesterGroup->id,
            'role' => AssetAccessRole::REQUESTER,
        ]);

        $approverGroups = $this->asset->approverUserGroups;

        $this->assertCount(1, $approverGroups);
        $this->assertTrue($approverGroups->contains($approverGroup));
        $this->assertFalse($approverGroups->contains($requesterGroup));
    }

    public function test_asset_requester_user_groups_filters_by_role(): void
    {
        $approverGroup = UserGroup::factory()->create();
        $requesterGroup = UserGroup::factory()->create();

        AssetAccessGrant::factory()->create([
            'asset_id' => $this->asset->id,
            'user_group_id' => $approverGroup->id,
            'role' => AssetAccessRole::APPROVER,
        ]);

        AssetAccessGrant::factory()->create([
            'asset_id' => $this->asset->id,
            'user_group_id' => $requesterGroup->id,
            'role' => AssetAccessRole::REQUESTER,
        ]);

        $requesterGroups = $this->asset->requesterUserGroups;

        $this->assertCount(1, $requesterGroups);
        $this->assertTrue($requesterGroups->contains($requesterGroup));
        $this->assertFalse($requesterGroups->contains($approverGroup));
    }

    public function test_asset_approver_users_filters_by_role(): void
    {
        $approverUser = User::factory()->create();
        $requesterUser = User::factory()->create();

        AssetAccessGrant::factory()->create([
            'asset_id' => $this->asset->id,
            'user_id' => $approverUser->id,
            'role' => AssetAccessRole::APPROVER,
        ]);

        AssetAccessGrant::factory()->create([
            'asset_id' => $this->asset->id,
            'user_id' => $requesterUser->id,
            'role' => AssetAccessRole::REQUESTER,
        ]);

        $approverUsers = $this->asset->approverUsers;

        $this->assertCount(1, $approverUsers);
        $this->assertTrue($approverUsers->contains($approverUser));
        $this->assertFalse($approverUsers->contains($requesterUser));
    }

    public function test_asset_requester_users_filters_by_role(): void
    {
        $approverUser = User::factory()->create();
        $requesterUser = User::factory()->create();

        AssetAccessGrant::factory()->create([
            'asset_id' => $this->asset->id,
            'user_id' => $approverUser->id,
            'role' => AssetAccessRole::APPROVER,
        ]);

        AssetAccessGrant::factory()->create([
            'asset_id' => $this->asset->id,
            'user_id' => $requesterUser->id,
            'role' => AssetAccessRole::REQUESTER,
        ]);

        $requesterUsers = $this->asset->requesterUsers;

        $this->assertCount(1, $requesterUsers);
        $this->assertTrue($requesterUsers->contains($requesterUser));
        $this->assertFalse($requesterUsers->contains($approverUser));
    }

    public function test_asset_soft_deletes(): void
    {
        $this->assertFalse($this->asset->trashed());

        $this->asset->delete();

        $this->assertTrue($this->asset->fresh()->trashed());
        $this->assertNotNull($this->asset->fresh()->deleted_at);
    }

    public function test_asset_uses_traits(): void
    {
        $traits = class_uses_recursive(Asset::class);

        $this->assertContains('App\Traits\BelongsToOrganization', $traits);
        $this->assertContains('App\Traits\HasBlamable', $traits);
        $this->assertContains('App\Traits\HasStatus', $traits);
        $this->assertContains('Illuminate\Database\Eloquent\Factories\HasFactory', $traits);
        $this->assertContains('Illuminate\Database\Eloquent\SoftDeletes', $traits);
    }

    public function test_asset_attribute_labels_are_defined(): void
    {
        $expectedLabels = [
            'org_id' => 'Organization',
            'name' => 'Name',
            'description' => 'Description',
            'status' => 'Status',
            'host' => 'Host',
            'port' => 'Port',
            'dbms' => 'DBMS',
        ];

        $this->assertEquals($expectedLabels, Asset::$attributeLabels);
    }

    public function test_asset_includable_relationships_are_defined(): void
    {
        $expectedIncludable = [
            'org',
            'accounts',
            'accessGrants',
            'requests',
            'sessions',
            'users',
            'userGroups',
            'approverUserGroups',
            'requesterUserGroups',
            'approverUsers',
            'requesterUsers',
            'createdBy',
            'updatedBy',
        ];

        $this->assertEquals($expectedIncludable, Asset::$includable);
    }
}
