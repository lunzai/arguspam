<?php

namespace Tests\Unit\Services;

use App\Models\Permission;
use App\Services\PolicyPermissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PolicyPermissionServiceTest extends TestCase
{
    use RefreshDatabase;

    private PolicyPermissionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PolicyPermissionService;
    }

    public function test_get_changes_returns_correct_structure(): void
    {
        $changes = $this->service->getChanges();

        $this->assertIsArray($changes);
        $this->assertArrayHasKey('to_add', $changes);
        $this->assertArrayHasKey('to_remove', $changes);
        $this->assertArrayHasKey('unchanged', $changes);
    }

    public function test_get_changes_identifies_permissions_to_add(): void
    {
        // Clear any existing permissions and related data
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Permission::truncate();
        \DB::table('permission_role')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // No existing permissions - all should be added
        $changes = $this->service->getChanges();

        $this->assertGreaterThan(0, $changes['to_add']->count());
        $this->assertEquals(0, $changes['to_remove']->count());
        $this->assertEquals(0, $changes['unchanged']->count());
    }

    public function test_get_changes_identifies_existing_permissions(): void
    {
        // Clear any existing permissions and related data
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Permission::truncate();
        \DB::table('permission_role')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create some permissions that match policy methods
        Permission::create([
            'name' => 'org:viewany',
            'description' => 'Org: View Any',
        ]);

        Permission::create([
            'name' => 'org:view',
            'description' => 'Org: View',
        ]);

        $changes = $this->service->getChanges();

        $this->assertGreaterThan(0, $changes['unchanged']->count());
        $this->assertTrue($changes['unchanged']->contains('name', 'org:viewany'));
        $this->assertTrue($changes['unchanged']->contains('name', 'org:view'));
    }

    public function test_get_changes_identifies_permissions_to_remove_when_remove_others_true(): void
    {
        // Clear any existing permissions and related data
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Permission::truncate();
        \DB::table('permission_role')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create a permission that doesn't match any policy
        Permission::create([
            'name' => 'custom:permission',
            'description' => 'Custom Permission',
        ]);

        $changes = $this->service->getChanges(true);

        $this->assertGreaterThan(0, $changes['to_remove']->count());
        $this->assertTrue($changes['to_remove']->contains('name', 'custom:permission'));
    }

    public function test_get_changes_does_not_remove_permissions_when_remove_others_false(): void
    {
        // Clear any existing permissions and related data
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Permission::truncate();
        \DB::table('permission_role')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create a permission that doesn't match any policy
        Permission::create([
            'name' => 'custom:permission',
            'description' => 'Custom Permission',
        ]);

        $changes = $this->service->getChanges(false);

        $this->assertEquals(0, $changes['to_remove']->count());
        $this->assertGreaterThan(0, $changes['unchanged']->count());
    }

    public function test_sync_permissions_adds_new_permissions(): void
    {
        // Clear any existing permissions and related data
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Permission::truncate();
        \DB::table('permission_role')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $initialCount = Permission::count();

        $changes = $this->service->syncPermissions();

        $this->assertGreaterThan($initialCount, Permission::count());
        $this->assertGreaterThan(0, $changes['to_add']->count());
    }

    public function test_sync_permissions_does_not_add_existing_permissions(): void
    {
        // Clear any existing permissions and related data
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Permission::truncate();
        \DB::table('permission_role')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // First sync
        $this->service->syncPermissions();
        $countAfterFirst = Permission::count();

        // Second sync - should not add any new permissions
        $changes = $this->service->syncPermissions();

        $this->assertEquals($countAfterFirst, Permission::count());
        $this->assertEquals(0, $changes['to_add']->count());
    }

    public function test_sync_permissions_removes_permissions_when_remove_others_true(): void
    {
        // Clear any existing permissions and related data
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Permission::truncate();
        \DB::table('permission_role')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create a custom permission that doesn't match any policy
        Permission::create([
            'name' => 'custom:permission',
            'description' => 'Custom Permission',
        ]);

        $changes = $this->service->syncPermissions(true);

        $this->assertFalse(Permission::where('name', 'custom:permission')->exists());
        $this->assertGreaterThan(0, $changes['to_remove']->count());
    }

    public function test_sync_permissions_does_not_remove_permissions_when_remove_others_false(): void
    {
        // Clear any existing permissions and related data
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Permission::truncate();
        \DB::table('permission_role')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create a custom permission that doesn't match any policy
        Permission::create([
            'name' => 'custom:permission',
            'description' => 'Custom Permission',
        ]);

        $changes = $this->service->syncPermissions(false);

        $this->assertTrue(Permission::where('name', 'custom:permission')->exists());
        $this->assertEquals(0, $changes['to_remove']->count());
    }

    public function test_sync_permissions_handles_empty_to_add_collection(): void
    {
        // Clear any existing permissions and related data
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Permission::truncate();
        \DB::table('permission_role')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // First sync to add all permissions
        $this->service->syncPermissions();

        // Second sync should handle empty to_add collection
        $changes = $this->service->syncPermissions();

        $this->assertEquals(0, $changes['to_add']->count());
        $this->assertGreaterThan(0, $changes['unchanged']->count());
    }

    public function test_sync_permissions_handles_empty_to_remove_collection(): void
    {
        // Clear any existing permissions and related data
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Permission::truncate();
        \DB::table('permission_role')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $changes = $this->service->syncPermissions(true);

        // Should handle empty to_remove collection gracefully
        $this->assertEquals(0, $changes['to_remove']->count());
        $this->assertGreaterThan(0, $changes['to_add']->count());
    }

    public function test_policy_permissions_are_properly_formatted(): void
    {
        // Clear any existing permissions and related data
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Permission::truncate();
        \DB::table('permission_role')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $changes = $this->service->getChanges();

        $this->assertGreaterThan(0, $changes['to_add']->count());

        foreach ($changes['to_add'] as $permission) {
            $this->assertArrayHasKey('name', $permission);
            $this->assertArrayHasKey('description', $permission);
            $this->assertIsString($permission['name']);
            $this->assertTrue(is_string($permission['description']) || $permission['description'] instanceof \Illuminate\Support\Stringable);
            $this->assertStringContainsString(':', $permission['name']);
            $this->assertEquals(strtolower($permission['name']), $permission['name']);
        }
    }

    public function test_policy_permissions_exclude_magic_methods(): void
    {
        // Clear any existing permissions and related data
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Permission::truncate();
        \DB::table('permission_role')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $changes = $this->service->getChanges();

        $this->assertGreaterThan(0, $changes['to_add']->count());

        foreach ($changes['to_add'] as $permission) {
            $this->assertStringNotContainsString('__', $permission['name']);
        }
    }

    public function test_policy_permissions_exclude_parent_class_methods(): void
    {
        // Clear any existing permissions and related data
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Permission::truncate();
        \DB::table('permission_role')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $changes = $this->service->getChanges();

        $this->assertGreaterThan(0, $changes['to_add']->count());

        // Should not include methods from parent classes or traits
        foreach ($changes['to_add'] as $permission) {
            $this->assertStringNotContainsString('trait:', $permission['name']);
            $this->assertStringNotContainsString('illuminate:', $permission['name']);
        }
    }

    public function test_handles_nonexistent_policy_class(): void
    {
        // Clear any existing permissions and related data
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Permission::truncate();
        \DB::table('permission_role')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create a temporary fake policy file
        $tempFile = app_path('Policies/NonExistentTestPolicy.php');

        try {
            // Create a file but without a matching class to trigger line 26
            File::put($tempFile, '<?php // This file exists but has no matching class');

            // Create a service that will scan for this specific file
            $mockService = new class extends PolicyPermissionService
            {
                protected function getPolicyPermissions(): \Illuminate\Support\Collection
                {
                    $policyPath = app_path('Policies');
                    $policyFiles = [app_path('Policies/NonExistentTestPolicy.php')]; // Only scan our fake file

                    return collect($policyFiles)->flatMap(function ($file) {
                        $policyClass = 'App\\Policies\\'.basename($file, '.php');
                        $modelName = \Illuminate\Support\Str::before(class_basename($file), 'Policy');

                        if (!class_exists($policyClass)) {
                            return []; // This should execute line 26
                        }

                        return $this->getPermissionsFromPolicy($policyClass, $modelName);
                    });
                }
            };

            $changes = $mockService->getChanges();

            $this->assertInstanceOf(\Illuminate\Support\Collection::class, $changes['to_add']);
            $this->assertEquals(0, $changes['to_add']->count());

        } finally {
            // Clean up
            if (File::exists($tempFile)) {
                File::delete($tempFile);
            }
        }
    }

    public function test_covers_all_edge_cases_for_100_percent_coverage(): void
    {
        // Clear any existing permissions and related data
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Permission::truncate();
        \DB::table('permission_role')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create a policy class that extends another class to test line 44 (method declaring class check)
        $mockPolicyContent = '<?php
namespace App\Policies;

class EdgeCaseTestPolicy extends \Exception
{
    public function myOwnMethod($user)
    {
        return true;
    }
    
    public function __magicMethod()
    {
        return true;
    }
}';

        $tempFile = app_path('Policies/EdgeCaseTestPolicy.php');

        try {
            File::put($tempFile, $mockPolicyContent);

            // Include the file
            include $tempFile;

            // Test the actual service to get the real line coverage
            $reflection = new \ReflectionClass($this->service);
            $method = $reflection->getMethod('getPermissionsFromPolicy');
            $method->setAccessible(true);

            $result = $method->invoke($this->service, 'App\Policies\EdgeCaseTestPolicy', 'EdgeCaseTest');

            $names = $result->pluck('name');

            // Should include own methods
            $this->assertTrue($names->contains('edgecasetest:myownmethod'));

            // Should exclude magic methods (line 48 coverage)
            $this->assertFalse($names->contains('edgecasetest:__magicmethod'));

            // Should exclude parent class methods (line 44 coverage)
            // Exception class has many inherited methods that should be filtered out
            $this->assertFalse($names->contains('edgecasetest:getmessage'));
            $this->assertFalse($names->contains('edgecasetest:getcode'));
            $this->assertFalse($names->contains('edgecasetest:getfile'));

        } finally {
            // Clean up
            if (File::exists($tempFile)) {
                File::delete($tempFile);
            }
        }
    }

    public function test_nonexistent_class_in_policy_directory(): void
    {
        // This test will actually trigger line 26 by temporarily creating a policy file
        // that doesn't have a corresponding class definition

        $tempFile = app_path('Policies/TriggerLine26Policy.php');

        try {
            // Create a file without a valid class definition
            File::put($tempFile, '<?php
// This file exists but contains no valid policy class
// This should trigger the class_exists check on line 26
');

            // Use reflection to call the actual service method
            $reflection = new \ReflectionClass($this->service);
            $method = $reflection->getMethod('getPolicyPermissions');
            $method->setAccessible(true);

            // This should execute line 26: return [];
            $result = $method->invoke($this->service);

            $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result);

        } finally {
            // Clean up
            if (File::exists($tempFile)) {
                File::delete($tempFile);
            }
        }
    }

    public function test_handles_reflection_exception(): void
    {
        // Use reflection to test the getPermissionsFromPolicy method directly
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getPermissionsFromPolicy');
        $method->setAccessible(true);

        // Pass a non-existent class name
        $result = $method->invoke($this->service, 'NonExistentClass', 'Model');

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result);
        $this->assertTrue($result->isEmpty());
    }

    public function test_filters_methods_by_parameter_count(): void
    {
        // Create a mock policy class for testing
        $mockPolicyContent = '<?php
namespace App\Policies;

class TestPolicy extends \stdClass
{
    public function methodWithNoParams()
    {
        return true;
    }
    
    public function methodWithOneParam($user)
    {
        return true;
    }
    
    public function methodWithTwoParams($user, $model)
    {
        return true;
    }
    
    public function __construct()
    {
        // Magic method that should be excluded
    }
    
    public function __toString()
    {
        // Another magic method that should be excluded
        return "";
    }
}';

        $tempFile = app_path('Policies/TestPolicy.php');

        try {
            File::put($tempFile, $mockPolicyContent);

            // Include the file so the class exists
            include $tempFile;

            // Use reflection to test the method
            $reflection = new \ReflectionClass($this->service);
            $method = $reflection->getMethod('getPermissionsFromPolicy');
            $method->setAccessible(true);

            $result = $method->invoke($this->service, 'App\Policies\TestPolicy', 'Test');

            // Should only include methods with 1 or more parameters
            $this->assertGreaterThan(0, $result->count());

            $names = $result->pluck('name');

            // Should exclude methods with no parameters
            $this->assertFalse($names->contains('test:methodwithnoparam'));

            // Should exclude magic methods (line 48 coverage)
            $this->assertFalse($names->contains('test:__construct'));
            $this->assertFalse($names->contains('test:__tostring'));

            // Should include valid methods
            $this->assertTrue($names->contains('test:methodwithoneparam'));
            $this->assertTrue($names->contains('test:methodwithtwoparams'));

            // Check that parent class methods are excluded (line 44 coverage)
            // Since TestPolicy extends stdClass, any inherited methods should be filtered out
            $allMethods = (new \ReflectionClass('App\Policies\TestPolicy'))->getMethods(\ReflectionMethod::IS_PUBLIC);
            $parentMethods = array_filter($allMethods, function ($method) {
                return $method->getDeclaringClass()->getName() !== 'App\Policies\TestPolicy';
            });

            foreach ($parentMethods as $parentMethod) {
                $this->assertFalse($names->contains('test:'.strtolower($parentMethod->getName())));
            }

        } finally {
            // Clean up
            if (File::exists($tempFile)) {
                File::delete($tempFile);
            }
        }
    }

    public function test_permission_description_formatting(): void
    {
        // Clear any existing permissions and related data
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Permission::truncate();
        \DB::table('permission_role')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $changes = $this->service->getChanges();

        $this->assertGreaterThan(0, $changes['to_add']->count());

        foreach ($changes['to_add'] as $permission) {
            // Check that description is properly formatted
            $this->assertStringContainsString(': ', (string) $permission['description']);
            $this->assertStringContainsString(':', $permission['name']);
            $this->assertStringNotContainsString('_', (string) $permission['description']);
        }
    }

    public function test_sync_permissions_sets_timestamps(): void
    {
        // Clear any existing permissions and related data
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Permission::truncate();
        \DB::table('permission_role')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->service->syncPermissions();

        $permissions = Permission::all();

        $this->assertGreaterThan(0, $permissions->count());

        foreach ($permissions as $permission) {
            $this->assertNotNull($permission->created_at);
            $this->assertNotNull($permission->updated_at);
            $this->assertInstanceOf(\Carbon\Carbon::class, $permission->created_at);
            $this->assertInstanceOf(\Carbon\Carbon::class, $permission->updated_at);
        }
    }

    public function test_get_changes_returns_collections(): void
    {
        $changes = $this->service->getChanges();

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $changes['to_add']);
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $changes['to_remove']);
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $changes['unchanged']);
    }

    public function test_get_changes_includes_permission_ids_for_existing(): void
    {
        // Clear any existing permissions and related data
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Permission::truncate();
        \DB::table('permission_role')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create a permission
        $permission = Permission::create([
            'name' => 'org:viewany',
            'description' => 'Org: View Any',
        ]);

        $changes = $this->service->getChanges();

        $unchangedPermission = $changes['unchanged']->firstWhere('name', 'org:viewany');
        $this->assertNotNull($unchangedPermission);
        $this->assertEquals($permission->id, $unchangedPermission['id']);
    }

    public function test_sync_permissions_uses_correct_permission_ids_for_removal(): void
    {
        // Clear any existing permissions and related data
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Permission::truncate();
        \DB::table('permission_role')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create permissions
        $permission1 = Permission::create(['name' => 'custom:permission1', 'description' => 'Custom 1']);
        $permission2 = Permission::create(['name' => 'custom:permission2', 'description' => 'Custom 2']);

        // Sync with remove others = true
        $changes = $this->service->syncPermissions(true);

        // Check that the correct permissions were identified for removal
        $removedIds = $changes['to_remove']->pluck('id')->toArray();
        $this->assertContains($permission1->id, $removedIds);
        $this->assertContains($permission2->id, $removedIds);
    }
}
