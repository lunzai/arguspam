<?php

namespace Tests\Integration\OrgAi;

use App\Models\Org;
use App\Models\OrgAiProvider;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrgAiProviderControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Org $org;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->org = Org::factory()->create();
        $this->org->users()->attach($this->user->id);
    }

    #[Test]
    public function it_lists_org_ai_providers_for_current_org(): void
    {
        $this->giveUserPermission('org:view');
        OrgAiProvider::factory()->create([
            'org_id' => $this->org->id,
            'driver' => 'openai',
        ]);

        $response = $this->actingAs($this->user)->withHeaders([
            'x-organization-id' => (string) $this->org->id,
        ])->getJson('/org-ai-providers');

        $response->assertOk();
        $response->assertJsonPath('data.0.attributes.driver', 'openai');
    }

    #[Test]
    public function it_stores_an_org_ai_provider(): void
    {
        $this->giveUserPermission('org:updateany');
        $response = $this->actingAs($this->user)->withHeaders([
            'x-organization-id' => (string) $this->org->id,
        ])->postJson('/org-ai-providers', [
            'driver' => 'openai',
            'api_key' => 'sk-test-key-'.str_repeat('a', 20),
            'label' => 'primary',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.attributes.driver', 'openai');
        $this->assertDatabaseHas('org_ai_providers', [
            'org_id' => $this->org->id,
            'driver' => 'openai',
        ]);
    }

    private function giveUserPermission(string $permissionName): void
    {
        $permission = Permission::firstOrCreate(
            ['name' => $permissionName],
            ['description' => ucfirst(str_replace(':', ' ', $permissionName))]
        );
        $role = Role::factory()->create();
        $role->permissions()->attach($permission);
        $this->user->roles()->attach($role);
        $this->user->clearUserRolePermissionCache();
    }
}
