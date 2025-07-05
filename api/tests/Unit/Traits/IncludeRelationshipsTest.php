<?php

namespace Tests\Unit\Traits;

use App\Models\User;
use App\Models\UserGroup;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Asset;
use App\Traits\IncludeRelationships;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Tests\TestCase;

class IncludeRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    private TestController $controller;
    private User $user;
    private UserGroup $userGroup;
    private Role $role;
    private Permission $permission;
    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->controller = new TestController();
        
        // Create test data
        $this->user = User::factory()->create();
        $this->userGroup = UserGroup::factory()->create();
        $this->role = Role::factory()->create();
        $this->permission = Permission::factory()->create();
        $this->asset = Asset::factory()->create();
        
        // Set up relationships
        $this->user->userGroups()->attach($this->userGroup->id);
        $this->user->roles()->attach($this->role->id);
        $this->role->permissions()->attach($this->permission->id);
    }

    public function test_apply_includes_with_no_include_parameter(): void
    {
        $request = new Request();
        $query = User::query();
        
        $result = $this->controller->applyIncludes($query, $request);
        
        $this->assertInstanceOf(Builder::class, $result);
        $this->assertSame($query, $result);
        $this->assertEmpty($query->getEagerLoads());
    }

    public function test_apply_includes_with_empty_include_parameter(): void
    {
        $request = new Request(['include' => '']);
        $query = User::query();
        
        $result = $this->controller->applyIncludes($query, $request);
        
        $this->assertInstanceOf(Builder::class, $result);
        $this->assertEmpty($query->getEagerLoads());
    }

    public function test_apply_includes_with_single_valid_relation(): void
    {
        $request = new Request(['include' => 'userGroups']);
        $query = User::query();
        
        $result = $this->controller->applyIncludes($query, $request);
        
        $this->assertInstanceOf(Builder::class, $result);
        $this->assertArrayHasKey('userGroups', $query->getEagerLoads());
    }

    public function test_apply_includes_with_multiple_valid_relations(): void
    {
        $request = new Request(['include' => 'userGroups,roles']);
        $query = User::query();
        
        $result = $this->controller->applyIncludes($query, $request);
        
        $this->assertInstanceOf(Builder::class, $result);
        $eagerLoads = $query->getEagerLoads();
        $this->assertArrayHasKey('userGroups', $eagerLoads);
        $this->assertArrayHasKey('roles', $eagerLoads);
    }

    public function test_apply_includes_with_whitespace_in_relations(): void
    {
        $request = new Request(['include' => ' userGroups , roles ']);
        $query = User::query();
        
        $result = $this->controller->applyIncludes($query, $request);
        
        $this->assertInstanceOf(Builder::class, $result);
        $eagerLoads = $query->getEagerLoads();
        $this->assertArrayHasKey('userGroups', $eagerLoads);
        $this->assertArrayHasKey('roles', $eagerLoads);
    }

    public function test_apply_includes_with_invalid_relation(): void
    {
        $request = new Request(['include' => 'invalidRelation']);
        $query = User::query();
        
        $result = $this->controller->applyIncludes($query, $request);
        
        $this->assertInstanceOf(Builder::class, $result);
        $this->assertEmpty($query->getEagerLoads());
    }

    public function test_apply_includes_with_mix_of_valid_and_invalid_relations(): void
    {
        $request = new Request(['include' => 'userGroups,invalidRelation,roles']);
        $query = User::query();
        
        $result = $this->controller->applyIncludes($query, $request);
        
        $this->assertInstanceOf(Builder::class, $result);
        $eagerLoads = $query->getEagerLoads();
        $this->assertArrayHasKey('userGroups', $eagerLoads);
        $this->assertArrayHasKey('roles', $eagerLoads);
        $this->assertArrayNotHasKey('invalidRelation', $eagerLoads);
    }

    public function test_apply_includes_with_nested_relation(): void
    {
        $request = new Request(['include' => 'roles.permissions']);
        $query = User::query();
        
        $result = $this->controller->applyIncludes($query, $request);
        
        $this->assertInstanceOf(Builder::class, $result);
        $this->assertArrayHasKey('roles.permissions', $query->getEagerLoads());
    }

    public function test_apply_includes_with_invalid_nested_relation(): void
    {
        $request = new Request(['include' => 'roles.invalidRelation']);
        $query = User::query();
        
        $result = $this->controller->applyIncludes($query, $request);
        
        $this->assertInstanceOf(Builder::class, $result);
        $this->assertEmpty($query->getEagerLoads());
    }

    public function test_get_includable_relations_with_existing_property(): void
    {
        $relations = $this->controller->getIncludableRelations(User::class);
        
        $this->assertIsArray($relations);
        $this->assertNotEmpty($relations);
        $this->assertContains('userGroups', $relations);
        $this->assertContains('roles', $relations);
    }

    public function test_get_includable_relations_with_non_existing_property(): void
    {
        $relations = $this->controller->getIncludableRelations(TestModelWithoutIncludable::class);
        
        $this->assertIsArray($relations);
        $this->assertEmpty($relations);
    }

    public function test_is_valid_relation_with_valid_single_relation(): void
    {
        $isValid = $this->controller->isValidRelation(User::class, 'userGroups');
        
        $this->assertTrue($isValid);
    }

    public function test_is_valid_relation_with_invalid_single_relation(): void
    {
        $isValid = $this->controller->isValidRelation(User::class, 'invalidRelation');
        
        $this->assertFalse($isValid);
    }

    public function test_is_valid_relation_with_valid_nested_relation(): void
    {
        $isValid = $this->controller->isValidRelation(User::class, 'roles.permissions');
        
        $this->assertTrue($isValid);
    }

    public function test_is_valid_relation_with_invalid_nested_relation_first_level(): void
    {
        $isValid = $this->controller->isValidRelation(User::class, 'invalidRelation.permissions');
        
        $this->assertFalse($isValid);
    }

    public function test_is_valid_relation_with_invalid_nested_relation_second_level(): void
    {
        $isValid = $this->controller->isValidRelation(User::class, 'roles.invalidRelation');
        
        $this->assertFalse($isValid);
    }

    public function test_is_valid_relation_with_non_existent_relation_method(): void
    {
        $isValid = $this->controller->isValidRelation(User::class, 'nonExistentMethod');
        
        $this->assertFalse($isValid);
    }

    public function test_is_valid_relation_with_non_existent_method_in_nested_relation(): void
    {
        // This tests the specific case on lines 66-67 where method_exists returns false
        // We need a relation that's in the includable array but doesn't actually exist as a method
        // Let's modify Role's includable array to include a non-existent method
        
        // Temporarily add a fake relation to Role's includable array
        $originalIncludable = Role::$includable;
        Role::$includable = array_merge($originalIncludable, ['fakeRelation']);
        
        try {
            $isValid = $this->controller->isValidRelation(User::class, 'roles.fakeRelation');
            $this->assertFalse($isValid);
        } finally {
            // Restore original includable array
            Role::$includable = $originalIncludable;
        }
    }

    public function test_is_valid_relation_with_method_that_exists_but_not_in_includable(): void
    {
        // Test a method that exists on the model but is not in the includable array
        // This should return false because it's not whitelisted
        $isValid = $this->controller->isValidRelation(User::class, 'sessions');
        
        // Check if sessions is in User::$includable
        $includable = User::$includable ?? [];
        if (in_array('sessions', $includable)) {
            $this->assertTrue($isValid);
        } else {
            $this->assertFalse($isValid);
        }
    }

    public function test_is_valid_relation_with_model_without_includable_property(): void
    {
        $isValid = $this->controller->isValidRelation(TestModelWithoutIncludable::class, 'someRelation');
        
        $this->assertTrue($isValid);
    }

    public function test_is_valid_relation_with_deep_nested_relation(): void
    {
        // Test User -> roles -> permissions (3 levels)
        $isValid = $this->controller->isValidRelation(User::class, 'roles.permissions');
        
        $this->assertTrue($isValid);
    }

    public function test_is_valid_relation_handles_model_instantiation_correctly(): void
    {
        // This tests that the trait correctly instantiates models to check relationships
        $isValid = $this->controller->isValidRelation(User::class, 'userGroups');
        
        $this->assertTrue($isValid);
    }

    public function test_apply_includes_integration_with_actual_query(): void
    {
        $request = new Request(['include' => 'userGroups,roles']);
        $query = User::query();
        
        $this->controller->applyIncludes($query, $request);
        
        // Execute the query to ensure it works correctly
        $users = $query->get();
        
        $this->assertNotEmpty($users);
        foreach ($users as $user) {
            $this->assertTrue($user->relationLoaded('userGroups'));
            $this->assertTrue($user->relationLoaded('roles'));
        }
    }

    public function test_apply_includes_integration_with_nested_relations(): void
    {
        $request = new Request(['include' => 'roles.permissions']);
        $query = User::query();
        
        $this->controller->applyIncludes($query, $request);
        
        // Execute the query to ensure it works correctly
        $users = $query->get();
        
        $this->assertNotEmpty($users);
        foreach ($users as $user) {
            $this->assertTrue($user->relationLoaded('roles'));
            foreach ($user->roles as $role) {
                $this->assertTrue($role->relationLoaded('permissions'));
            }
        }
    }

    public function test_apply_includes_with_complex_include_combinations(): void
    {
        $request = new Request(['include' => 'userGroups,roles.permissions,assetAccessGrants']);
        $query = User::query();
        
        $result = $this->controller->applyIncludes($query, $request);
        
        $eagerLoads = $query->getEagerLoads();
        $this->assertArrayHasKey('userGroups', $eagerLoads);
        $this->assertArrayHasKey('roles.permissions', $eagerLoads);
        $this->assertArrayHasKey('assetAccessGrants', $eagerLoads);
    }

    public function test_apply_includes_ignores_relations_not_in_includable_list(): void
    {
        // Test with a relation that exists on the model but is not in the includable list
        $request = new Request(['include' => 'sessions']); // sessions exists but may not be in includable
        $query = User::query();
        
        $result = $this->controller->applyIncludes($query, $request);
        
        $eagerLoads = $query->getEagerLoads();
        
        // Check if sessions is in the User::$includable array
        $includable = User::$includable;
        if (in_array('sessions', $includable)) {
            $this->assertArrayHasKey('sessions', $eagerLoads);
        } else {
            $this->assertArrayNotHasKey('sessions', $eagerLoads);
        }
    }

    public function test_trait_methods_return_correct_types(): void
    {
        $request = new Request(['include' => 'userGroups']);
        $query = User::query();
        
        // Test return types
        $builderResult = $this->controller->applyIncludes($query, $request);
        $this->assertInstanceOf(Builder::class, $builderResult);
        
        $relations = $this->controller->getIncludableRelations(User::class);
        $this->assertIsArray($relations);
        
        $isValid = $this->controller->isValidRelation(User::class, 'userGroups');
        $this->assertIsBool($isValid);
    }

    public function test_include_parameter_case_sensitivity(): void
    {
        $request = new Request(['include' => 'usergroups']); // lowercase
        $query = User::query();
        
        $result = $this->controller->applyIncludes($query, $request);
        
        // Should not include the relation as it's case-sensitive
        $this->assertEmpty($query->getEagerLoads());
    }

    public function test_empty_segments_in_nested_relations(): void
    {
        $request = new Request(['include' => 'roles.']);
        $query = User::query();
        
        $result = $this->controller->applyIncludes($query, $request);
        
        // Should not include the relation due to empty segment
        $this->assertEmpty($query->getEagerLoads());
    }

    public function test_is_valid_relation_with_method_that_does_not_return_relation(): void
    {
        // This tests when a method exists but doesn't return a proper relation instance
        // Should cause an error when trying to call getRelated() on a non-relation
        $this->expectException(\Error::class);
        
        $this->controller->isValidRelation(TestModelWithNonRelationMethod::class, 'nonRelationMethod');
    }
}

// Test controller class using the trait
class TestController
{
    use IncludeRelationships {
        applyIncludes as public;
        getIncludableRelations as public;
        isValidRelation as public;
    }
}

// Test model without includable property
class TestModelWithoutIncludable extends Model
{
    protected $table = 'test_models';
    
    public function someRelation()
    {
        return $this->hasMany(User::class);
    }
}

// Test model with a method that exists but doesn't return a relation
class TestModelWithNonRelationMethod extends Model
{
    protected $table = 'test_models';
    
    public static $includable = ['nonRelationMethod'];
    
    public function nonRelationMethod()
    {
        // This method exists but doesn't return a relation instance
        return 'not a relation';
    }
}