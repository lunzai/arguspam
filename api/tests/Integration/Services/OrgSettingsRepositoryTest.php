<?php

namespace Tests\Integration\Services;

use App\Models\Org;
use App\Services\OrgSettingsRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrgSettingsRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private OrgSettingsRepository $repo;
    private Org $org;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new OrgSettingsRepository;
        $this->org = Org::factory()->create();
    }

    public function test_get_returns_default_when_key_does_not_exist(): void
    {
        $result = $this->repo->get($this->org->id, 'nonexistent_key', 'default_value');

        $this->assertEquals('default_value', $result);
    }

    public function test_get_returns_null_default_when_not_specified(): void
    {
        $result = $this->repo->get($this->org->id, 'nonexistent_key');

        $this->assertNull($result);
    }

    public function test_set_creates_new_setting(): void
    {
        $this->repo->set($this->org->id, 'theme', 'dark');

        $result = $this->repo->get($this->org->id, 'theme');
        $this->assertEquals('dark', $result);
    }

    public function test_set_updates_existing_setting(): void
    {
        $this->repo->set($this->org->id, 'theme', 'light');
        $this->repo->set($this->org->id, 'theme', 'dark');

        $result = $this->repo->get($this->org->id, 'theme');
        $this->assertEquals('dark', $result);
    }

    public function test_settings_are_scoped_to_org(): void
    {
        $otherOrg = Org::factory()->create();
        $this->repo->set($this->org->id, 'feature', 'enabled');
        $this->repo->set($otherOrg->id, 'feature', 'disabled');

        $this->assertEquals('enabled', $this->repo->get($this->org->id, 'feature'));
        $this->assertEquals('disabled', $this->repo->get($otherOrg->id, 'feature'));
    }
}
