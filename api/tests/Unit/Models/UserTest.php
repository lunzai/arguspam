<?php

namespace Tests\Unit\Models;

use App\Enums\AssetAccessRole;
use App\Http\Filters\QueryFilter;
use App\Models\ActionAudit;
use App\Models\Asset;
use App\Models\AssetAccessGrant;
use App\Models\Org;
use App\Models\User;
use App\Models\UserAccessRestriction;
use App\Models\UserGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request as HttpRequest;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Org $org;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->org = Org::factory()->create();
    }

    public function test_in_org_returns_true_when_user_belongs_to_org(): void
    {
        $this->user->orgs()->attach($this->org);

        $this->assertTrue($this->user->fresh()->inOrg($this->org));
    }

    public function test_in_org_returns_false_when_user_does_not_belong_to_org(): void
    {
        $this->assertFalse($this->user->inOrg($this->org));
    }

    public function test_all_requester_assets_returns_assets_with_requester_role(): void
    {
        $asset1 = Asset::factory()->create();
        $asset2 = Asset::factory()->create();
        $asset3 = Asset::factory()->create();

        // Direct user access
        $asset1->accessGrants()->create([
            'user_id' => $this->user->id,
            'role' => AssetAccessRole::REQUESTER,
        ]);

        // Approver access (should not be included)
        $asset2->accessGrants()->create([
            'user_id' => $this->user->id,
            'role' => AssetAccessRole::APPROVER,
        ]);

        // Group access
        $userGroup = UserGroup::factory()->create();
        $this->user->userGroups()->attach($userGroup);

        $asset3->accessGrants()->create([
            'user_group_id' => $userGroup->id,
            'role' => AssetAccessRole::REQUESTER,
        ]);

        $requesterAssets = $this->user->fresh()->allRequesterAssets()->get();

        $this->assertCount(2, $requesterAssets);
        $this->assertTrue($requesterAssets->contains($asset1));
        $this->assertTrue($requesterAssets->contains($asset3));
        $this->assertFalse($requesterAssets->contains($asset2));
    }

    public function test_all_approver_assets_returns_assets_with_approver_role(): void
    {
        $asset1 = Asset::factory()->create();
        $asset2 = Asset::factory()->create();
        $asset3 = Asset::factory()->create();

        // Direct user access
        $asset1->accessGrants()->create([
            'user_id' => $this->user->id,
            'role' => AssetAccessRole::APPROVER,
        ]);

        // Requester access (should not be included)
        $asset2->accessGrants()->create([
            'user_id' => $this->user->id,
            'role' => AssetAccessRole::REQUESTER,
        ]);

        // Group access
        $userGroup = UserGroup::factory()->create();
        $this->user->userGroups()->attach($userGroup);

        $asset3->accessGrants()->create([
            'user_group_id' => $userGroup->id,
            'role' => AssetAccessRole::APPROVER,
        ]);

        $approverAssets = $this->user->fresh()->allApproverAssets()->get();

        $this->assertCount(2, $approverAssets);
        $this->assertTrue($approverAssets->contains($asset1));
        $this->assertTrue($approverAssets->contains($asset3));
        $this->assertFalse($approverAssets->contains($asset2));
    }

    public function test_all_assets_returns_all_accessible_assets(): void
    {
        $asset1 = Asset::factory()->create();
        $asset2 = Asset::factory()->create();
        $asset3 = Asset::factory()->create();
        $asset4 = Asset::factory()->create();

        // Direct user access - requester
        $asset1->accessGrants()->create([
            'user_id' => $this->user->id,
            'role' => AssetAccessRole::REQUESTER,
        ]);

        // Direct user access - approver
        $asset2->accessGrants()->create([
            'user_id' => $this->user->id,
            'role' => AssetAccessRole::APPROVER,
        ]);

        // Group access
        $userGroup = UserGroup::factory()->create();
        $this->user->userGroups()->attach($userGroup);

        $asset3->accessGrants()->create([
            'user_group_id' => $userGroup->id,
            'role' => AssetAccessRole::REQUESTER,
        ]);

        // No access to asset4

        $allAssets = $this->user->fresh()->allAssets()->get();

        $this->assertCount(3, $allAssets);
        $this->assertTrue($allAssets->contains($asset1));
        $this->assertTrue($allAssets->contains($asset2));
        $this->assertTrue($allAssets->contains($asset3));
        $this->assertFalse($allAssets->contains($asset4));
    }

    public function test_user_has_correct_fillable_attributes(): void
    {
        $expectedFillable = [
            'name',
            'email',
            'status',
        ];

        $this->assertEquals($expectedFillable, $this->user->getFillable());
    }

    public function test_user_has_correct_hidden_attributes(): void
    {
        $expectedHidden = [
            'password',
            'remember_token',
            'deleted_at',
            'deleted_by',
            'two_factor_secret',
            'two_factor_recovery_codes',
        ];

        $this->assertEquals($expectedHidden, $this->user->getHidden());
    }

    public function test_user_has_correct_casts(): void
    {
        $casts = $this->user->getCasts();

        $this->assertArrayHasKey('email_verified_at', $casts);
        $this->assertArrayHasKey('last_login_at', $casts);
        $this->assertArrayHasKey('two_factor_enabled', $casts);
        $this->assertArrayHasKey('password', $casts);
        $this->assertArrayHasKey('status', $casts);
    }

    public function test_filter_scope_applies_query_filter(): void
    {
        // Test line 87: return $filter->apply($builder);
        $user1 = User::factory()->create(['name' => 'Alice']);
        $user2 = User::factory()->create(['name' => 'Bob']);

        // Create a mock filter
        $request = new HttpRequest(['name' => 'Alice']);
        $filter = new TestUserQueryFilter($request);

        $results = User::filter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Alice', $results->first()->name);
    }

    public function test_requester_assets_relationship(): void
    {
        // Test lines 119-122: requesterAssets() relationship method
        $asset1 = Asset::factory()->create();
        $asset2 = Asset::factory()->create();

        // Create requester access grant
        AssetAccessGrant::factory()->create([
            'user_id' => $this->user->id,
            'asset_id' => $asset1->id,
            'role' => AssetAccessRole::REQUESTER,
        ]);

        // Create approver access grant (should not be included)
        AssetAccessGrant::factory()->create([
            'user_id' => $this->user->id,
            'asset_id' => $asset2->id,
            'role' => AssetAccessRole::APPROVER,
        ]);

        $requesterAssets = $this->user->requesterAssets;

        $this->assertCount(1, $requesterAssets);
        $this->assertTrue($requesterAssets->contains($asset1));
        $this->assertFalse($requesterAssets->contains($asset2));
    }

    public function test_approver_assets_relationship(): void
    {
        // Test lines 127-130: approverAssets() relationship method
        $asset1 = Asset::factory()->create();
        $asset2 = Asset::factory()->create();

        // Create approver access grant
        AssetAccessGrant::factory()->create([
            'user_id' => $this->user->id,
            'asset_id' => $asset1->id,
            'role' => AssetAccessRole::APPROVER,
        ]);

        // Create requester access grant (should not be included)
        AssetAccessGrant::factory()->create([
            'user_id' => $this->user->id,
            'asset_id' => $asset2->id,
            'role' => AssetAccessRole::REQUESTER,
        ]);

        $approverAssets = $this->user->approverAssets;

        $this->assertCount(1, $approverAssets);
        $this->assertTrue($approverAssets->contains($asset1));
        $this->assertFalse($approverAssets->contains($asset2));
    }

    public function test_requests_relationship(): void
    {
        // Test line 177: requests() relationship method
        $relationship = $this->user->requests();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relationship);
        $this->assertEquals('requester_id', $relationship->getForeignKeyName());
        $this->assertEquals('App\Models\Request', $relationship->getRelated()::class);
    }

    public function test_session_audits_relationship(): void
    {
        // Test line 187: sessionAudits() relationship method
        // Just test that the relationship method exists and returns HasMany
        $relationship = $this->user->sessionAudits();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relationship);
        $this->assertEquals('user_id', $relationship->getForeignKeyName());
        $this->assertEquals('App\Models\SessionAudit', $relationship->getRelated()::class);
    }

    public function test_access_restrictions_relationship(): void
    {
        // Test line 192: accessRestrictions() relationship method
        // Create restrictions manually since factory doesn't exist
        $restriction1 = new UserAccessRestriction;
        $restriction1->user_id = $this->user->id;
        $restriction1->type = \App\Enums\AccessRestrictionType::IP_ADDRESS;
        $restriction1->value = ['allowed_ips' => ['192.168.1.0/24']];
        $restriction1->status = \App\Enums\Status::ACTIVE;
        $restriction1->timestamps = false;
        $restriction1->save();

        $restriction2 = new UserAccessRestriction;
        $restriction2->user_id = $this->user->id;
        $restriction2->type = \App\Enums\AccessRestrictionType::TIME_WINDOW;
        $restriction2->value = ['days' => [1, 2, 3, 4, 5], 'start_time' => '09:00', 'end_time' => '17:00'];
        $restriction2->status = \App\Enums\Status::ACTIVE;
        $restriction2->timestamps = false;
        $restriction2->save();

        $otherUser = User::factory()->create();
        $restriction3 = new UserAccessRestriction;
        $restriction3->user_id = $otherUser->id;
        $restriction3->type = \App\Enums\AccessRestrictionType::IP_ADDRESS;
        $restriction3->value = ['allowed_ips' => ['10.0.0.0/8']];
        $restriction3->status = \App\Enums\Status::ACTIVE;
        $restriction3->timestamps = false;
        $restriction3->save();

        $userRestrictions = $this->user->accessRestrictions;

        $this->assertCount(2, $userRestrictions);
        $this->assertTrue($userRestrictions->contains($restriction1));
        $this->assertTrue($userRestrictions->contains($restriction2));
        $this->assertFalse($userRestrictions->contains($restriction3));
    }

    public function test_action_audits_relationship(): void
    {
        // Test line 197: actionAudits() relationship method
        // Create action audits manually to avoid factory dependency issues
        $audit1 = new ActionAudit;
        $audit1->org_id = $this->org->id;
        $audit1->user_id = $this->user->id;
        $audit1->action_type = \App\Enums\AuditAction::CREATE;
        $audit1->entity_type = 'test_entity';
        $audit1->entity_id = 1;
        $audit1->description = 'Test audit 1';
        $audit1->timestamps = false;
        $audit1->save();

        $audit2 = new ActionAudit;
        $audit2->org_id = $this->org->id;
        $audit2->user_id = $this->user->id;
        $audit2->action_type = \App\Enums\AuditAction::UPDATE;
        $audit2->entity_type = 'test_entity';
        $audit2->entity_id = 2;
        $audit2->description = 'Test audit 2';
        $audit2->timestamps = false;
        $audit2->save();

        $otherUser = User::factory()->create();
        $audit3 = new ActionAudit;
        $audit3->org_id = $this->org->id;
        $audit3->user_id = $otherUser->id;
        $audit3->action_type = \App\Enums\AuditAction::DELETE;
        $audit3->entity_type = 'test_entity';
        $audit3->entity_id = 3;
        $audit3->description = 'Test audit 3';
        $audit3->timestamps = false;
        $audit3->save();

        $userActionAudits = $this->user->actionAudits;

        $this->assertCount(2, $userActionAudits);
        $this->assertTrue($userActionAudits->contains($audit1));
        $this->assertTrue($userActionAudits->contains($audit2));
        $this->assertFalse($userActionAudits->contains($audit3));
    }

    public function test_user_uses_correct_traits(): void
    {
        $traits = class_uses_recursive(User::class);

        $this->assertContains('Laravel\\Sanctum\\HasApiTokens', $traits);
        $this->assertContains('App\\Traits\\HasBlamable', $traits);
        $this->assertContains('Illuminate\\Database\\Eloquent\\Factories\\HasFactory', $traits);
        $this->assertContains('App\\Traits\\HasRbac', $traits);
        $this->assertContains('App\\Traits\\HasStatus', $traits);
        $this->assertContains('Illuminate\\Notifications\\Notifiable', $traits);
    }

    public function test_user_includable_relationships_are_defined(): void
    {
        $expectedIncludable = [
            'orgs',
            'userGroups',
            'assetAccessGrants',
            'approverAssetAccessGrants',
            'requesterAssetAccessGrants',
            'requests',
            'sessions',
            'accessRestrictions',
            'roles',
            'permissions',
        ];

        $this->assertEquals($expectedIncludable, User::$includable);
    }

    public function test_user_attribute_labels_are_defined(): void
    {
        $expectedLabels = [
            'name' => 'Name',
            'email' => 'Email',
            'password' => 'Password',
            'status' => 'Status',
            'two_factor_enabled' => 'MFA',
            'last_login_at' => 'Last Login At',
        ];

        $this->assertEquals($expectedLabels, User::$attributeLabels);
    }

    public function test_orgs_relationship(): void
    {
        $org1 = Org::factory()->create();
        $org2 = Org::factory()->create();
        $org3 = Org::factory()->create();

        $this->user->orgs()->attach([$org1->id, $org2->id]);

        $userOrgs = $this->user->fresh()->orgs;

        $this->assertCount(2, $userOrgs);
        $this->assertTrue($userOrgs->contains($org1));
        $this->assertTrue($userOrgs->contains($org2));
        $this->assertFalse($userOrgs->contains($org3));
    }

    public function test_user_groups_relationship(): void
    {
        $group1 = UserGroup::factory()->create();
        $group2 = UserGroup::factory()->create();
        $group3 = UserGroup::factory()->create();

        $this->user->userGroups()->attach([$group1->id, $group2->id]);

        $userGroups = $this->user->fresh()->userGroups;

        $this->assertCount(2, $userGroups);
        $this->assertTrue($userGroups->contains($group1));
        $this->assertTrue($userGroups->contains($group2));
        $this->assertFalse($userGroups->contains($group3));
    }

    public function test_asset_access_grants_relationship(): void
    {
        $asset1 = Asset::factory()->create();
        $asset2 = Asset::factory()->create();

        $grant1 = AssetAccessGrant::factory()->create([
            'user_id' => $this->user->id,
            'asset_id' => $asset1->id,
            'role' => AssetAccessRole::REQUESTER,
        ]);

        $grant2 = AssetAccessGrant::factory()->create([
            'user_id' => $this->user->id,
            'asset_id' => $asset2->id,
            'role' => AssetAccessRole::APPROVER,
        ]);

        // Grant for different user - need to provide either user_id or user_group_id
        $otherUser = User::factory()->create();
        AssetAccessGrant::factory()->create([
            'user_id' => $otherUser->id,
            'asset_id' => $asset1->id,
            'role' => AssetAccessRole::REQUESTER,
        ]);

        $userGrants = $this->user->assetAccessGrants;

        $this->assertCount(2, $userGrants);
        $this->assertTrue($userGrants->contains($grant1));
        $this->assertTrue($userGrants->contains($grant2));
    }
}

// Test QueryFilter for testing the filter scope
class TestUserQueryFilter extends QueryFilter
{
    public function name(string $value): Builder
    {
        return $this->builder->where('name', $value);
    }
}
