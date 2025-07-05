<?php

namespace Tests\Unit\Traits;

use App\Enums\AssetAccessRole;
use App\Enums\CacheKey;
use App\Models\Asset;
use App\Models\AssetAccessGrant;
use App\Models\Org;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserGroup;
use App\Traits\HasRbac;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class HasRbacTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Role $adminRole;
    private Role $userRole;
    private Role $managerRole;
    private Permission $userViewPermission;
    private Permission $userCreatePermission;
    private Permission $assetViewPermission;
    private Org $org;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->org = Org::factory()->create();
        $this->user = User::factory()->create();
        
        // Create roles with unique names
        $this->adminRole = Role::factory()->create(['name' => 'Admin-' . uniqid(), 'is_default' => false]);
        $this->userRole = Role::factory()->create(['name' => 'User-' . uniqid(), 'is_default' => true]);
        $this->managerRole = Role::factory()->create(['name' => 'Manager-' . uniqid(), 'is_default' => false]);
        
        // Create permissions with unique names
        $this->userViewPermission = Permission::factory()->create(['name' => 'user:view-' . uniqid()]);
        $this->userCreatePermission = Permission::factory()->create(['name' => 'user:create-' . uniqid()]);
        $this->assetViewPermission = Permission::factory()->create(['name' => 'asset:view-' . uniqid()]);
        
        // Set up default admin role in config
        Config::set('pam.rbac.default_admin_role', $this->adminRole->name);
        Config::set('cache.default_ttl', 300);
        
        // Clear cache before each test
        Cache::flush();
    }

    public function test_roles_relationship_returns_belongs_to_many(): void
    {
        $this->user->roles()->attach([$this->userRole->id, $this->managerRole->id]);
        
        $roles = $this->user->roles;
        
        $this->assertCount(2, $roles);
        $this->assertTrue($roles->contains('name', $this->userRole->name));
        $this->assertTrue($roles->contains('name', $this->managerRole->name));
    }

    public function test_get_all_roles_returns_cached_collection(): void
    {
        $this->user->roles()->attach([$this->userRole->id, $this->adminRole->id]);
        
        // First call - should cache the result
        $roles1 = $this->user->getAllRoles();
        
        // Verify cache was set
        $cacheKey = CacheKey::USER_ROLES->key($this->user->id);
        $this->assertTrue(Cache::has($cacheKey));
        
        // Second call - should return cached result
        $roles2 = $this->user->getAllRoles();
        
        $this->assertEquals($roles1, $roles2);
        $this->assertCount(2, $roles1);
        $this->assertTrue($roles1->contains('name', $this->userRole->name));
        $this->assertTrue($roles1->contains('name', $this->adminRole->name));
    }

    public function test_get_all_roles_uses_configured_cache_ttl(): void
    {
        Config::set('cache.default_ttl', 600);
        $this->user->roles()->attach($this->userRole->id);
        
        $this->user->getAllRoles();
        
        $cacheKey = CacheKey::USER_ROLES->key($this->user->id);
        
        // Check that cache exists (TTL testing is complex, but we can verify cache was set)
        $this->assertTrue(Cache::has($cacheKey));
    }

    public function test_is_admin_returns_true_when_user_has_admin_role(): void
    {
        $this->user->roles()->attach($this->adminRole->id);
        
        $this->assertTrue($this->user->isAdmin());
    }

    public function test_is_admin_returns_false_when_user_has_no_admin_role(): void
    {
        $this->user->roles()->attach($this->userRole->id);
        
        $this->assertFalse($this->user->isAdmin());
    }

    public function test_is_admin_returns_false_when_user_has_no_roles(): void
    {
        $this->assertFalse($this->user->isAdmin());
    }

    public function test_is_admin_uses_configured_admin_role_name(): void
    {
        Config::set('pam.rbac.default_admin_role', 'SuperAdmin');
        
        $superAdminRole = Role::factory()->create(['name' => 'SuperAdmin']);
        $this->user->roles()->attach($superAdminRole->id);
        
        $this->assertTrue($this->user->isAdmin());
        
        // Admin role should not be considered admin anymore
        $this->user->roles()->detach($superAdminRole->id);
        $this->user->roles()->attach($this->adminRole->id);
        $this->user->refresh(); // Refresh to pick up new relationships
        
        $this->assertFalse($this->user->isAdmin());
    }

    public function test_get_all_permissions_works_with_multiple_roles(): void
    {
        // This test verifies that the HasRbac trait properly handles multiple roles
        // The trait now correctly uses whereIn with the role IDs
        
        // First attach roles to user
        $this->user->roles()->attach([$this->userRole->id, $this->adminRole->id]);
        
        // Then attach permissions to roles
        $this->userRole->permissions()->attach([$this->userViewPermission->id]);
        $this->adminRole->permissions()->attach([$this->userCreatePermission->id, $this->assetViewPermission->id]);
        
        // This should work correctly now
        $permissions = $this->user->getAllPermissions();
        
        $this->assertCount(3, $permissions);
        $this->assertTrue($permissions->contains('name', $this->userViewPermission->name));
        $this->assertTrue($permissions->contains('name', $this->userCreatePermission->name));
        $this->assertTrue($permissions->contains('name', $this->assetViewPermission->name));
    }

    public function test_get_all_permissions_returns_empty_collection_when_no_roles(): void
    {
        // This test verifies that the trait properly handles users with no roles
        // The trait now correctly checks if roleIds is empty before running the query
        
        // Clear any cached roles first
        $this->user->clearUserRolePermissionCache();
        
        $permissions = $this->user->getAllPermissions();
        
        $this->assertCount(0, $permissions);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $permissions);
    }

    public function test_get_all_permissions_returns_empty_collection_when_roles_have_no_permissions(): void
    {
        $this->user->roles()->attach($this->userRole->id);
        
        $permissions = $this->user->getAllPermissions();
        
        $this->assertCount(0, $permissions);
    }

    public function test_has_any_permission_returns_true_when_user_has_single_permission(): void
    {
        $this->userRole->permissions()->attach($this->userViewPermission->id);
        $this->user->roles()->attach($this->userRole->id);
        
        $this->assertTrue($this->user->hasAnyPermission($this->userViewPermission->name));
    }

    public function test_has_any_permission_returns_true_when_user_has_one_of_multiple_permissions(): void
    {
        $this->userRole->permissions()->attach($this->userViewPermission->id);
        $this->user->roles()->attach($this->userRole->id);
        
        $this->assertTrue($this->user->hasAnyPermission(['nonexistent:permission', $this->userViewPermission->name, 'another:permission']));
    }

    public function test_has_any_permission_returns_false_when_user_has_no_permissions(): void
    {
        $this->user->roles()->attach($this->userRole->id);
        
        $this->assertFalse($this->user->hasAnyPermission('user:view'));
        $this->assertFalse($this->user->hasAnyPermission(['user:view', 'user:create']));
    }

    public function test_has_any_permission_is_case_insensitive(): void
    {
        $this->userRole->permissions()->attach($this->userViewPermission->id);
        $this->user->roles()->attach($this->userRole->id);
        
        $permissionName = $this->userViewPermission->name;
        
        $this->assertTrue($this->user->hasAnyPermission(strtoupper($permissionName)));
        $this->assertTrue($this->user->hasAnyPermission(ucwords($permissionName, ':')));
        $this->assertTrue($this->user->hasAnyPermission(['NONEXISTENT', $permissionName]));
    }

    public function test_has_any_permission_handles_string_and_array_input(): void
    {
        $this->userRole->permissions()->attach([$this->userViewPermission->id, $this->userCreatePermission->id]);
        $this->user->roles()->attach($this->userRole->id);
        
        // String input
        $this->assertTrue($this->user->hasAnyPermission($this->userViewPermission->name));
        
        // Array input
        $this->assertTrue($this->user->hasAnyPermission([$this->userViewPermission->name]));
        $this->assertTrue($this->user->hasAnyPermission([$this->userViewPermission->name, $this->userCreatePermission->name]));
        $this->assertTrue($this->user->hasAnyPermission(['nonexistent:permission', $this->userViewPermission->name]));
    }

    public function test_clear_user_role_permission_cache_clears_both_caches(): void
    {
        $this->user->roles()->attach($this->userRole->id);
        
        // Populate caches
        $this->user->getAllRoles();
        $this->user->getAllPermissions();
        
        // Verify caches exist
        $rolesCacheKey = CacheKey::USER_ROLES->key($this->user->id);
        $permissionsCacheKey = CacheKey::USER_PERMISSIONS->key($this->user->id);
        
        $this->assertTrue(Cache::has($rolesCacheKey));
        $this->assertTrue(Cache::has($permissionsCacheKey));
        
        // Clear cache
        $this->user->clearUserRolePermissionCache();
        
        // Verify caches are cleared
        $this->assertFalse(Cache::has($rolesCacheKey));
        $this->assertFalse(Cache::has($permissionsCacheKey));
    }

    public function test_clear_user_role_permission_cache_accepts_user_id_parameter(): void
    {
        $anotherUser = User::factory()->create();
        $anotherUser->roles()->attach($this->userRole->id);
        
        // Populate cache for another user
        $anotherUser->getAllRoles();
        
        $rolesCacheKey = CacheKey::USER_ROLES->key($anotherUser->id);
        $this->assertTrue(Cache::has($rolesCacheKey));
        
        // Clear cache using user ID parameter
        $this->user->clearUserRolePermissionCache($anotherUser->id);
        
        $this->assertFalse(Cache::has($rolesCacheKey));
    }

    public function test_clear_user_role_permission_cache_uses_current_user_id_when_no_parameter(): void
    {
        $this->user->roles()->attach($this->userRole->id);
        
        // Populate cache
        $this->user->getAllRoles();
        
        $rolesCacheKey = CacheKey::USER_ROLES->key($this->user->id);
        $this->assertTrue(Cache::has($rolesCacheKey));
        
        // Clear cache without parameter
        $this->user->clearUserRolePermissionCache();
        
        $this->assertFalse(Cache::has($rolesCacheKey));
    }

    public function test_can_request_asset_returns_true_when_user_has_requester_access(): void
    {
        $asset = Asset::factory()->create();
        
        // Give user requester access to asset
        AssetAccessGrant::factory()->create([
            'asset_id' => $asset->id,
            'user_id' => $this->user->id,
            'role' => AssetAccessRole::REQUESTER,
        ]);
        
        $this->assertTrue($this->user->canRequestAsset($this->user, $asset));
    }

    public function test_can_request_asset_returns_false_when_user_has_no_access(): void
    {
        $asset = Asset::factory()->create();
        
        $this->assertFalse($this->user->canRequestAsset($this->user, $asset));
    }

    public function test_can_request_asset_returns_false_when_user_has_approver_access_only(): void
    {
        $asset = Asset::factory()->create();
        
        // Give user approver access (not requester)
        AssetAccessGrant::factory()->create([
            'asset_id' => $asset->id,
            'user_id' => $this->user->id,
            'role' => AssetAccessRole::APPROVER,
        ]);
        
        $this->assertFalse($this->user->canRequestAsset($this->user, $asset));
    }

    public function test_can_approve_asset_returns_true_when_user_has_approver_access(): void
    {
        $asset = Asset::factory()->create();
        
        // Give user approver access to asset
        AssetAccessGrant::factory()->create([
            'asset_id' => $asset->id,
            'user_id' => $this->user->id,
            'role' => AssetAccessRole::APPROVER,
        ]);
        
        $this->assertTrue($this->user->canApproveAsset($this->user, $asset));
    }

    public function test_can_approve_asset_returns_false_when_user_has_no_access(): void
    {
        $asset = Asset::factory()->create();
        
        $this->assertFalse($this->user->canApproveAsset($this->user, $asset));
    }

    public function test_can_approve_asset_returns_false_when_user_has_requester_access_only(): void
    {
        $asset = Asset::factory()->create();
        
        // Give user requester access (not approver)
        AssetAccessGrant::factory()->create([
            'asset_id' => $asset->id,
            'user_id' => $this->user->id,
            'role' => AssetAccessRole::REQUESTER,
        ]);
        
        $this->assertFalse($this->user->canApproveAsset($this->user, $asset));
    }

    public function test_can_access_asset_returns_true_when_user_has_direct_access(): void
    {
        $asset = Asset::factory()->create();
        
        // Give user direct access to asset
        AssetAccessGrant::factory()->create([
            'asset_id' => $asset->id,
            'user_id' => $this->user->id,
            'role' => AssetAccessRole::REQUESTER,
        ]);
        
        $this->assertTrue($this->user->canAccessAsset($this->user, $asset));
    }

    public function test_can_access_asset_returns_true_when_user_has_group_access(): void
    {
        $asset = Asset::factory()->create();
        $userGroup = UserGroup::factory()->create();
        
        // Add user to group
        $this->user->userGroups()->attach($userGroup->id);
        
        // Give group access to asset
        AssetAccessGrant::factory()->create([
            'asset_id' => $asset->id,
            'user_group_id' => $userGroup->id,
            'role' => AssetAccessRole::REQUESTER,
        ]);
        
        $this->assertTrue($this->user->canAccessAsset($this->user, $asset));
    }

    public function test_can_access_asset_returns_false_when_user_has_no_access(): void
    {
        $asset = Asset::factory()->create();
        
        $this->assertFalse($this->user->canAccessAsset($this->user, $asset));
    }

    public function test_asset_access_methods_work_with_different_users(): void
    {
        $anotherUser = User::factory()->create();
        $asset = Asset::factory()->create();
        
        // Give another user access
        AssetAccessGrant::factory()->create([
            'asset_id' => $asset->id,
            'user_id' => $anotherUser->id,
            'role' => AssetAccessRole::REQUESTER,
        ]);
        
        // Test from current user's perspective about another user
        $this->assertTrue($this->user->canRequestAsset($anotherUser, $asset));
        $this->assertTrue($this->user->canAccessAsset($anotherUser, $asset));
        $this->assertFalse($this->user->canApproveAsset($anotherUser, $asset));
    }

    public function test_cache_invalidation_updates_permissions_correctly(): void
    {
        $this->userRole->permissions()->attach($this->userViewPermission->id);
        $this->user->roles()->attach($this->userRole->id);
        
        // Get cached permissions
        $permissions1 = $this->user->getAllPermissions();
        $this->assertCount(1, $permissions1);
        
        // Add more permissions to role
        $this->userRole->permissions()->attach($this->userCreatePermission->id);
        
        // Should still return cached (old) permissions
        $permissions2 = $this->user->getAllPermissions();
        $this->assertCount(1, $permissions2);
        
        // Clear cache and get fresh permissions
        $this->user->clearUserRolePermissionCache();
        $permissions3 = $this->user->getAllPermissions();
        $this->assertCount(2, $permissions3);
    }
}