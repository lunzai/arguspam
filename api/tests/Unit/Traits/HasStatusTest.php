<?php

namespace Tests\Unit\Traits;

use App\Enums\Status;
use App\Models\Org;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HasStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_active_returns_true_for_active_org(): void
    {
        $org = Org::factory()->create(['status' => Status::ACTIVE->value]);

        $this->assertTrue($org->isActive());
    }

    public function test_is_active_returns_false_for_inactive_org(): void
    {
        $org = Org::factory()->create(['status' => Status::INACTIVE->value]);

        $this->assertFalse($org->isActive());
    }

    public function test_is_inactive_returns_true_for_inactive_org(): void
    {
        $org = Org::factory()->create(['status' => Status::INACTIVE->value]);

        $this->assertTrue($org->isInactive());
    }

    public function test_is_inactive_returns_false_for_active_org(): void
    {
        $org = Org::factory()->create(['status' => Status::ACTIVE->value]);

        $this->assertFalse($org->isInactive());
    }

    public function test_active_scope_filters_to_active_orgs(): void
    {
        Org::factory()->create(['status' => Status::ACTIVE->value]);
        Org::factory()->create(['status' => Status::INACTIVE->value]);

        $activeOrgs = Org::query()->active()->get();

        $this->assertTrue($activeOrgs->every(fn ($o) => $o->isActive()));
    }
}
