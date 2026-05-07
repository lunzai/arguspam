<?php

namespace Tests\Integration\Services;

use App\Models\Permission;
use App\Services\PolicyPermissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_get_changes_returns_expected_structure(): void
    {
        $changes = $this->service->getChanges();

        $this->assertArrayHasKey('to_add', $changes);
        $this->assertArrayHasKey('to_remove', $changes);
        $this->assertArrayHasKey('unchanged', $changes);
    }

    public function test_get_changes_detects_new_permissions(): void
    {
        // Start with empty DB — all policy permissions are "to_add"
        $changes = $this->service->getChanges();

        $this->assertNotEmpty($changes['to_add']);
        $this->assertEmpty($changes['to_remove']);
    }

    public function test_get_changes_with_existing_permissions(): void
    {
        // Seed one existing permission that matches a policy
        $changes = $this->service->getChanges();
        $first = $changes['to_add']->first();

        Permission::create([
            'name' => $first['name'],
            'description' => $first['description'],
        ]);

        $changes = $this->service->getChanges();

        // That permission is now "unchanged" (existing), not "to_add"
        $this->assertFalse($changes['to_add']->contains('name', $first['name']));
        $this->assertTrue($changes['unchanged']->contains('name', $first['name']));
    }

    public function test_get_changes_remove_others_false_does_not_flag_extra(): void
    {
        Permission::create(['name' => 'orphan:permission', 'description' => 'Orphan']);

        $changes = $this->service->getChanges(false);

        $this->assertEmpty($changes['to_remove']);
    }

    public function test_get_changes_remove_others_true_flags_extra_permissions(): void
    {
        Permission::create(['name' => 'orphan:permission', 'description' => 'Orphan']);

        $changes = $this->service->getChanges(true);

        $this->assertTrue($changes['to_remove']->contains('name', 'orphan:permission'));
    }

    public function test_sync_permissions_creates_new_permissions(): void
    {
        $countBefore = Permission::count();

        $changes = $this->service->syncPermissions();

        $this->assertGreaterThan($countBefore, Permission::count());
        $this->assertNotEmpty($changes['to_add']);
    }

    public function test_sync_permissions_is_idempotent(): void
    {
        $this->service->syncPermissions();
        $countAfterFirst = Permission::count();

        $this->service->syncPermissions();

        $this->assertEquals($countAfterFirst, Permission::count());
    }

    public function test_sync_permissions_with_remove_others_deletes_orphans(): void
    {
        Permission::create(['name' => 'orphan:delete_me', 'description' => 'Delete me']);

        $this->service->syncPermissions(true);

        $this->assertNull(Permission::where('name', 'orphan:delete_me')->first());
    }
}
