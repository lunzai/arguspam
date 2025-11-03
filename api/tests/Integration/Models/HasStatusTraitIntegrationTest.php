<?php

namespace Tests\Unit\Models;

use App\Enums\Status;
use App\Models\Asset;
use App\Models\Org;
use App\Models\UserGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HasStatusTraitIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_org_has_status_trait_methods(): void
    {
        $activeOrg = Org::factory()->create(['status' => Status::ACTIVE]);
        $inactiveOrg = Org::factory()->create(['status' => Status::INACTIVE]);

        $this->assertTrue($activeOrg->isActive());
        $this->assertFalse($activeOrg->isInactive());

        $this->assertFalse($inactiveOrg->isActive());
        $this->assertTrue($inactiveOrg->isInactive());
    }

    public function test_user_group_has_status_trait_methods(): void
    {
        $activeUserGroup = UserGroup::factory()->create(['status' => Status::ACTIVE]);
        $inactiveUserGroup = UserGroup::factory()->create(['status' => Status::INACTIVE]);

        $this->assertTrue($activeUserGroup->isActive());
        $this->assertFalse($activeUserGroup->isInactive());

        $this->assertFalse($inactiveUserGroup->isActive());
        $this->assertTrue($inactiveUserGroup->isInactive());
    }

    public function test_asset_has_status_trait_methods(): void
    {
        $activeAsset = Asset::factory()->create(['status' => Status::ACTIVE]);
        $inactiveAsset = Asset::factory()->create(['status' => Status::INACTIVE]);

        $this->assertTrue($activeAsset->isActive());
        $this->assertFalse($activeAsset->isInactive());

        $this->assertFalse($inactiveAsset->isActive());
        $this->assertTrue($inactiveAsset->isInactive());
    }

    public function test_models_use_has_status_trait(): void
    {
        $orgTraits = class_uses(Org::class);
        $userGroupTraits = class_uses(UserGroup::class);
        $assetTraits = class_uses(Asset::class);

        $this->assertArrayHasKey('App\\Traits\\HasStatus', $orgTraits);
        $this->assertArrayHasKey('App\\Traits\\HasStatus', $userGroupTraits);
        $this->assertArrayHasKey('App\\Traits\\HasStatus', $assetTraits);
    }
}
