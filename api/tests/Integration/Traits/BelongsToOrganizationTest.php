<?php

namespace Tests\Unit\Traits;

use App\Enums\Dbms;
use App\Enums\Status;
use App\Models\Asset;
use App\Models\Org;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class BelongsToOrganizationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Org $org1;
    private Org $org2;
    private Request $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->org1 = Org::factory()->create();
        $this->org2 = Org::factory()->create();

        $this->request = new Request;
        $this->app->instance('request', $this->request);
    }

    public function test_trait_automatically_scopes_queries_to_current_organization(): void
    {
        $this->setCurrentOrganization($this->org1->id);

        $asset1 = Asset::create([
            'name' => 'Asset 1',
            'host' => '192.168.1.1',
            'port' => 3306,
            'status' => Status::ACTIVE->value,
            'dbms' => Dbms::MYSQL->value,
        ]);

        $this->setCurrentOrganization($this->org2->id);

        $asset2 = Asset::create([
            'name' => 'Asset 2',
            'host' => '192.168.1.2',
            'port' => 3306,
            'status' => Status::ACTIVE->value,
            'dbms' => Dbms::MYSQL->value,
        ]);

        $this->setCurrentOrganization($this->org1->id);
        $assetsInOrg1 = Asset::all();

        $this->assertCount(1, $assetsInOrg1);
        $this->assertEquals($asset1->id, $assetsInOrg1->first()->id);
        $this->assertEquals($this->org1->id, $assetsInOrg1->first()->org_id);
    }

    public function test_trait_automatically_sets_org_id_when_creating_model(): void
    {
        $this->setCurrentOrganization($this->org1->id);

        $asset = Asset::create([
            'name' => 'Test Asset',
            'host' => '192.168.1.1',
            'port' => 3306,
            'status' => Status::ACTIVE->value,
            'dbms' => Dbms::MYSQL->value,
        ]);

        $this->assertEquals($this->org1->id, $asset->org_id);
    }

    public function test_trait_does_not_override_manually_set_org_id(): void
    {
        $this->setCurrentOrganization($this->org1->id);

        $asset = Asset::create([
            'name' => 'Test Asset',
            'host' => '192.168.1.1',
            'port' => 3306,
            'org_id' => $this->org2->id,
            'status' => Status::ACTIVE->value,
            'dbms' => Dbms::MYSQL->value,
        ]);

        $this->assertEquals($this->org2->id, $asset->org_id);
    }

    public function test_org_relationship_returns_correct_organization(): void
    {
        $this->setCurrentOrganization($this->org1->id);

        $asset = Asset::create([
            'name' => 'Test Asset',
            'host' => '192.168.1.1',
            'port' => 3306,
            'status' => Status::ACTIVE->value,
            'dbms' => Dbms::MYSQL->value,
        ]);

        $this->assertInstanceOf(Org::class, $asset->org);
        $this->assertEquals($this->org1->id, $asset->org->id);
        $this->assertEquals($this->org1->name, $asset->org->name);
    }

    public function test_for_organization_scope_filters_by_specific_org(): void
    {
        $this->setCurrentOrganization($this->org1->id);

        Asset::create(['name' => 'Asset 1', 'host' => '192.168.1.1', 'port' => 3306, 'status' => Status::ACTIVE->value, 'dbms' => Dbms::MYSQL->value]);

        $this->setCurrentOrganization($this->org2->id);

        Asset::create(['name' => 'Asset 2', 'host' => '192.168.1.2', 'port' => 3306, 'status' => Status::ACTIVE->value, 'dbms' => Dbms::MYSQL->value]);

        // Use withoutOrganizationScope to bypass global scope, then apply forOrganization
        $org1Assets = Asset::withoutOrganizationScope()->forOrganization($this->org1->id)->get();
        $org2Assets = Asset::withoutOrganizationScope()->forOrganization($this->org2->id)->get();

        $this->assertCount(1, $org1Assets);
        $this->assertCount(1, $org2Assets);
        $this->assertEquals($this->org1->id, $org1Assets->first()->org_id);
        $this->assertEquals($this->org2->id, $org2Assets->first()->org_id);
    }

    public function test_without_organization_scope_returns_all_records(): void
    {
        $this->setCurrentOrganization($this->org1->id);

        Asset::create(['name' => 'Asset 1', 'host' => '192.168.1.1', 'port' => 3306, 'status' => Status::ACTIVE->value, 'dbms' => Dbms::MYSQL->value]);

        $this->setCurrentOrganization($this->org2->id);

        Asset::create(['name' => 'Asset 2', 'host' => '192.168.1.2', 'port' => 3306, 'status' => Status::ACTIVE->value, 'dbms' => Dbms::MYSQL->value]);

        $allAssets = Asset::withoutOrganizationScope()->get();

        $this->assertCount(2, $allAssets);
    }

    public function test_is_in_organization_returns_correct_boolean(): void
    {
        $this->setCurrentOrganization($this->org1->id);

        $asset = Asset::create([
            'name' => 'Test Asset',
            'host' => '192.168.1.1',
            'port' => 3306,
            'status' => Status::ACTIVE->value,
            'dbms' => Dbms::MYSQL->value,
        ]);

        $this->assertTrue($asset->isInOrganization($this->org1->id));
        $this->assertFalse($asset->isInOrganization($this->org2->id));
    }

    public function test_is_in_current_organization_returns_correct_boolean(): void
    {
        $this->setCurrentOrganization($this->org1->id);

        $asset = Asset::create([
            'name' => 'Test Asset',
            'host' => '192.168.1.1',
            'port' => 3306,
            'status' => Status::ACTIVE->value,
            'dbms' => Dbms::MYSQL->value,
        ]);

        $this->assertTrue($asset->isInCurrentOrganization());

        $this->setCurrentOrganization($this->org2->id);

        $this->assertFalse($asset->isInCurrentOrganization());
    }

    public function test_is_in_current_organization_returns_false_when_no_context(): void
    {
        $this->clearCurrentOrganization();

        $asset = Asset::create([
            'name' => 'Test Asset',
            'host' => '192.168.1.1',
            'port' => 3306,
            'org_id' => $this->org1->id,
            'status' => Status::ACTIVE->value,
            'dbms' => Dbms::MYSQL->value,
        ]);

        $this->assertFalse($asset->isInCurrentOrganization());
    }

    public function test_get_current_organization_id_from_request_attribute(): void
    {
        $this->setCurrentOrganization($this->org1->id);

        $currentOrgId = Asset::getCurrentOrganizationId();

        $this->assertEquals($this->org1->id, $currentOrgId);
    }

    public function test_get_current_organization_id_from_header_fallback(): void
    {
        $this->request->headers->set('x-organization-id', $this->org1->id);

        $currentOrgId = Asset::getCurrentOrganizationId();

        $this->assertEquals($this->org1->id, $currentOrgId);
    }

    public function test_get_current_organization_id_returns_null_when_no_request(): void
    {
        // Simulate no request context (e.g., console commands) by binding null to request
        $originalRequest = $this->app['request'];
        $this->app->bind('request', function () {
            return null;
        });

        $currentOrgId = Asset::getCurrentOrganizationId();

        $this->assertNull($currentOrgId);

        // Restore original request
        $this->app->instance('request', $originalRequest);
    }

    public function test_get_current_organization_id_prefers_attribute_over_header(): void
    {
        $this->request->attributes->set('current_org_id', $this->org1->id);
        $this->request->headers->set('x-organization-id', $this->org2->id);

        $currentOrgId = Asset::getCurrentOrganizationId();

        $this->assertEquals($this->org1->id, $currentOrgId);
    }

    public function test_get_current_organization_id_returns_null_when_both_empty(): void
    {
        $this->clearCurrentOrganization();

        $currentOrgId = Asset::getCurrentOrganizationId();

        $this->assertNull($currentOrgId);
    }

    public function test_get_current_organization_id_uses_header_when_attribute_empty(): void
    {
        $this->request->attributes->set('current_org_id', '');
        $this->request->headers->set('x-organization-id', $this->org1->id);

        $currentOrgId = Asset::getCurrentOrganizationId();

        $this->assertEquals($this->org1->id, $currentOrgId);
    }

    public function test_get_current_organization_returns_organization_model(): void
    {
        $this->setCurrentOrganization($this->org1->id);

        $currentOrg = Asset::getCurrentOrganization();

        $this->assertInstanceOf(Org::class, $currentOrg);
        $this->assertEquals($this->org1->id, $currentOrg->id);
        $this->assertEquals($this->org1->name, $currentOrg->name);
    }

    public function test_get_current_organization_returns_null_when_no_context(): void
    {
        $this->clearCurrentOrganization();

        $currentOrg = Asset::getCurrentOrganization();

        $this->assertNull($currentOrg);
    }

    public function test_create_for_organization_creates_with_specific_org_id(): void
    {
        $asset = Asset::createForOrganization([
            'name' => 'Test Asset',
            'host' => '192.168.1.1',
            'port' => 3306,
            'status' => Status::ACTIVE->value,
            'dbms' => Dbms::MYSQL->value,
        ], $this->org1->id);

        $this->assertEquals($this->org1->id, $asset->org_id);
        $this->assertEquals('Test Asset', $asset->name);
    }

    public function test_create_for_organization_uses_current_org_when_no_org_id_provided(): void
    {
        $this->setCurrentOrganization($this->org1->id);

        $asset = Asset::createForOrganization([
            'name' => 'Test Asset',
            'host' => '192.168.1.1',
            'port' => 3306,
            'status' => Status::ACTIVE->value,
            'dbms' => Dbms::MYSQL->value,
        ]);

        $this->assertEquals($this->org1->id, $asset->org_id);
    }

    public function test_create_for_organization_throws_exception_when_no_org_id_available(): void
    {
        $this->clearCurrentOrganization();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Organization ID is required');

        Asset::createForOrganization([
            'name' => 'Test Asset',
            'host' => '192.168.1.1',
            'port' => 3306,
            'status' => Status::ACTIVE->value,
            'dbms' => Dbms::MYSQL->value,
        ]);
    }

    public function test_for_current_organization_returns_query_builder_for_current_org(): void
    {
        $this->setCurrentOrganization($this->org1->id);

        Asset::create(['name' => 'Asset 1', 'host' => '192.168.1.1', 'port' => 3306, 'status' => Status::ACTIVE->value, 'dbms' => Dbms::MYSQL->value]);

        $this->setCurrentOrganization($this->org2->id);

        Asset::create(['name' => 'Asset 2', 'host' => '192.168.1.2', 'port' => 3306, 'status' => Status::ACTIVE->value, 'dbms' => Dbms::MYSQL->value]);

        $this->setCurrentOrganization($this->org1->id);

        $currentOrgAssets = Asset::forCurrentOrganization()->get();

        $this->assertCount(1, $currentOrgAssets);
        $this->assertEquals($this->org1->id, $currentOrgAssets->first()->org_id);
    }

    public function test_for_current_organization_throws_exception_when_no_context(): void
    {
        $this->clearCurrentOrganization();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No current organization context available');

        Asset::forCurrentOrganization();
    }

    public function test_has_organization_context_returns_correct_boolean(): void
    {
        $this->setCurrentOrganization($this->org1->id);

        $this->assertTrue(Asset::hasOrganizationContext());

        $this->clearCurrentOrganization();

        $this->assertFalse(Asset::hasOrganizationContext());
    }

    public function test_trait_works_with_different_models(): void
    {
        $this->setCurrentOrganization($this->org1->id);

        $userGroup = UserGroup::create([
            'name' => 'Test Group',
            'description' => 'Test Description',
            'status' => Status::ACTIVE->value,
        ]);

        $this->assertEquals($this->org1->id, $userGroup->org_id);
        $this->assertTrue($userGroup->isInCurrentOrganization());
        $this->assertInstanceOf(Org::class, $userGroup->org);
    }

    public function test_global_scope_does_not_apply_when_no_organization_context(): void
    {
        $this->clearCurrentOrganization();

        $asset1 = Asset::create([
            'name' => 'Asset 1',
            'host' => '192.168.1.1',
            'port' => 3306,
            'status' => Status::ACTIVE->value,
            'org_id' => $this->org1->id,
            'dbms' => Dbms::MYSQL->value,
        ]);

        $asset2 = Asset::create([
            'name' => 'Asset 2',
            'host' => '192.168.1.2',
            'port' => 3306,
            'status' => Status::ACTIVE->value,
            'org_id' => $this->org2->id,
            'dbms' => Dbms::MYSQL->value,
        ]);

        $allAssets = Asset::all();

        $this->assertCount(2, $allAssets);
    }

    private function setCurrentOrganization(int $orgId): void
    {
        $this->request->attributes->set('current_org_id', $orgId);
    }

    private function clearCurrentOrganization(): void
    {
        $this->request->attributes->remove('current_org_id');
        $this->request->headers->remove('x-organization-id');
    }
}
