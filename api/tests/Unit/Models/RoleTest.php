<?php

namespace Tests\Unit\Models;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();

        $this->role = Role::factory()->create([
            'name' => 'Test Role',
            'description' => 'A test role for unit tests',
            'is_default' => false,
        ]);
    }

    public function test_role_has_correct_fillable_attributes(): void
    {
        $expectedFillable = [
            'name',
            'description',
        ];

        $this->assertEquals($expectedFillable, $this->role->getFillable());
    }

    public function test_role_has_correct_casts(): void
    {
        $casts = $this->role->getCasts();

        $this->assertArrayHasKey('is_default', $casts);
        $this->assertEquals('boolean', $casts['is_default']);
        $this->assertArrayHasKey('created_at', $casts);
        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertArrayHasKey('updated_at', $casts);
        $this->assertEquals('datetime', $casts['updated_at']);
    }

    public function test_role_attribute_labels_are_defined(): void
    {
        $expectedLabels = [
            'name' => 'Name',
            'description' => 'Description',
            'is_default' => 'Is Default',
        ];

        $this->assertEquals($expectedLabels, Role::$attributeLabels);
    }

    public function test_role_includable_relationships_are_defined(): void
    {
        $expectedIncludable = [
            'users',
            'permissions',
        ];

        $this->assertEquals($expectedIncludable, Role::$includable);
    }

    public function test_users_relationship(): void
    {
        // Test line 37: users() relationship method - this is the missing coverage
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        // Attach users to the role
        $this->role->users()->attach([$user1->id, $user2->id]);

        $roleUsers = $this->role->fresh()->users;

        $this->assertCount(2, $roleUsers);
        $this->assertTrue($roleUsers->contains($user1));
        $this->assertTrue($roleUsers->contains($user2));
        $this->assertFalse($roleUsers->contains($user3));

        // Test the relationship type
        $relationship = $this->role->users();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $relationship);
        $this->assertEquals('App\Models\User', $relationship->getRelated()::class);
    }

    public function test_permissions_relationship(): void
    {
        $permission1 = Permission::factory()->create(['name' => 'test:permission1']);
        $permission2 = Permission::factory()->create(['name' => 'test:permission2']);
        $permission3 = Permission::factory()->create(['name' => 'test:permission3']);

        // Attach permissions to the role
        $this->role->permissions()->attach([$permission1->id, $permission2->id]);

        $rolePermissions = $this->role->fresh()->permissions;

        $this->assertCount(2, $rolePermissions);
        $this->assertTrue($rolePermissions->contains($permission1));
        $this->assertTrue($rolePermissions->contains($permission2));
        $this->assertFalse($rolePermissions->contains($permission3));

        // Test the relationship type
        $relationship = $this->role->permissions();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $relationship);
        $this->assertEquals('App\Models\Permission', $relationship->getRelated()::class);
    }

    public function test_role_uses_correct_traits(): void
    {
        $traits = class_uses_recursive(Role::class);

        $this->assertContains('Illuminate\\Database\\Eloquent\\Factories\\HasFactory', $traits);
    }

    public function test_role_extends_base_model(): void
    {
        $this->assertInstanceOf(\App\Models\Model::class, $this->role);
    }

    public function test_role_creation_with_all_attributes(): void
    {
        $role = Role::factory()->create([
            'name' => 'Custom Role',
            'description' => 'A custom role with description',
            'is_default' => true,
        ]);

        $this->assertDatabaseHas('roles', [
            'name' => 'Custom Role',
            'description' => 'A custom role with description',
            'is_default' => true,
        ]);

        // Test that is_default is cast to boolean
        $this->assertTrue($role->is_default);
        $this->assertIsBool($role->is_default);
    }

    public function test_default_role_functionality(): void
    {
        // Test creating a default role
        $defaultRole = Role::factory()->create([
            'name' => 'Default Role',
            'is_default' => true,
        ]);

        // Test creating a non-default role
        $regularRole = Role::factory()->create([
            'name' => 'Regular Role',
            'is_default' => false,
        ]);

        $this->assertTrue($defaultRole->is_default);
        $this->assertFalse($regularRole->is_default);
    }

    public function test_role_can_be_attached_to_multiple_users(): void
    {
        $users = User::factory()->count(3)->create();

        // Attach all users to the role
        $this->role->users()->attach($users->pluck('id'));

        $this->assertCount(3, $this->role->fresh()->users);

        // Verify each user has the role
        foreach ($users as $user) {
            $this->assertTrue($user->fresh()->roles->contains($this->role));
        }
    }

    public function test_role_can_have_multiple_permissions(): void
    {
        $permissions = Permission::factory()->count(5)->create();

        // Attach all permissions to the role
        $this->role->permissions()->attach($permissions->pluck('id'));

        $rolePermissions = $this->role->fresh()->permissions;
        $this->assertCount(5, $rolePermissions);

        // Verify each permission is attached to the role
        foreach ($permissions as $permission) {
            $this->assertTrue($rolePermissions->contains($permission));
        }
    }

    public function test_role_relationships_can_be_detached(): void
    {
        $user = User::factory()->create();
        $permission = Permission::factory()->create();

        // Attach relationships
        $this->role->users()->attach($user->id);
        $this->role->permissions()->attach($permission->id);

        $this->assertCount(1, $this->role->fresh()->users);
        $this->assertCount(1, $this->role->fresh()->permissions);

        // Detach relationships
        $this->role->users()->detach($user->id);
        $this->role->permissions()->detach($permission->id);

        $this->assertCount(0, $this->role->fresh()->users);
        $this->assertCount(0, $this->role->fresh()->permissions);
    }

    public function test_role_name_and_description_are_stored_correctly(): void
    {
        $role = Role::factory()->create([
            'name' => 'Administrator',
            'description' => 'Full system access with all permissions',
        ]);

        $this->assertEquals('Administrator', $role->name);
        $this->assertEquals('Full system access with all permissions', $role->description);
    }
}
