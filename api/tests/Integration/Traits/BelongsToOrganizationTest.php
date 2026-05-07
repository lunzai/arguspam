<?php

namespace Tests\Integration\Traits;

use App\Models\Asset;
use App\Models\Org;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class BelongsToOrganizationTest extends TestCase
{
    use RefreshDatabase;

    private Org $org;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        $this->org = Org::factory()->create();
    }

    public function test_is_in_organization_returns_true_for_matching_org(): void
    {
        $asset = Asset::factory()->create(['org_id' => $this->org->id]);

        $this->assertTrue($asset->isInOrganization($this->org->id));
    }

    public function test_is_in_organization_returns_false_for_different_org(): void
    {
        $otherOrg = Org::factory()->create();
        $asset = Asset::factory()->create(['org_id' => $this->org->id]);

        $this->assertFalse($asset->isInOrganization($otherOrg->id));
    }

    public function test_scope_for_organization_filters_by_org(): void
    {
        $otherOrg = Org::factory()->create();
        Asset::factory()->create(['org_id' => $this->org->id]);
        Asset::factory()->create(['org_id' => $otherOrg->id]);

        $assets = Asset::withoutGlobalScopes()->forOrganization($this->org->id)->get();

        $this->assertTrue($assets->every(fn ($a) => $a->org_id === $this->org->id));
        $this->assertGreaterThanOrEqual(1, $assets->count());
    }

    public function test_scope_without_organization_scope_returns_all(): void
    {
        $otherOrg = Org::factory()->create();
        Asset::factory()->create(['org_id' => $this->org->id]);
        Asset::factory()->create(['org_id' => $otherOrg->id]);

        $assets = Asset::withoutOrganizationScope()->get();

        $this->assertGreaterThanOrEqual(2, $assets->count());
    }

    public function test_get_current_organization_id_returns_null_without_context(): void
    {
        $orgId = Asset::getCurrentOrganizationId();

        $this->assertNull($orgId);
    }

    public function test_get_current_organization_returns_null_without_context(): void
    {
        $org = Asset::getCurrentOrganization();

        $this->assertNull($org);
    }

    public function test_has_organization_context_returns_false_without_context(): void
    {
        $this->assertFalse(Asset::hasOrganizationContext());
    }

    public function test_create_for_organization_creates_model_with_org_id(): void
    {
        $user = User::factory()->create();
        $this->org->users()->attach($user->id);

        $asset = Asset::createForOrganization([
            'name' => 'Test Asset',
            'host' => '192.168.1.1',
            'port' => 5432,
            'dbms' => 'postgresql',
            'status' => 'active',
        ], $this->org->id);

        $this->assertEquals($this->org->id, $asset->org_id);
    }

    public function test_create_for_organization_throws_without_org_id(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Asset::createForOrganization(['name' => 'Test']);
    }

    public function test_org_relationship_returns_belongs_to(): void
    {
        $asset = Asset::factory()->create(['org_id' => $this->org->id]);

        $relatedOrg = $asset->org;

        $this->assertInstanceOf(Org::class, $relatedOrg);
        $this->assertEquals($this->org->id, $relatedOrg->id);
    }

    public function test_is_in_current_organization_with_context(): void
    {
        $asset = Asset::factory()->create(['org_id' => $this->org->id]);

        // Simulate org context via request attribute
        request()->merge([config('pam.org.request_attribute') => $this->org->id]);

        $result = $asset->isInCurrentOrganization();

        $this->assertTrue($result);

        // Clean up request state
        request()->replace([]);
    }

    public function test_for_current_organization_throws_without_context(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Asset::forCurrentOrganization();
    }

    public function test_for_current_organization_with_context(): void
    {
        Asset::factory()->create(['org_id' => $this->org->id]);

        request()->merge([config('pam.org.request_attribute') => $this->org->id]);

        $result = Asset::forCurrentOrganization()->get();

        $this->assertGreaterThanOrEqual(1, $result->count());

        request()->replace([]);
    }
}
