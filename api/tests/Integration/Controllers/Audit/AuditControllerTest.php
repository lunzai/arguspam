<?php

namespace Tests\Integration\Controllers\Audit;

use App\Models\ActionAudit;
use App\Models\Org;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Org $org;

    protected function setUp(): void
    {
        parent::setUp();
        $this->org = Org::factory()->create();
        $this->user = User::factory()->create();
        $this->org->users()->attach($this->user->id);
    }

    private function createAudit(): ActionAudit
    {
        return ActionAudit::create([
            'org_id' => $this->org->id,
            'user_id' => $this->user->id,
            'action_type' => 'create',
            'entity_type' => 'assets',
            'entity_id' => 1,
            'description' => 'Test audit entry',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /audits — index
    // -------------------------------------------------------------------------

    public function test_index_lists_audits_for_authorized_user(): void
    {
        $this->giveUserPermission($this->user, 'actionaudit:view');
        $this->createAudit();

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/audits')
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_index_returns_403_without_permission(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->getJson('/audits')
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // GET /audits/{id} — show
    // -------------------------------------------------------------------------

    public function test_show_returns_audit_for_authorized_user(): void
    {
        $this->giveUserPermission($this->user, 'actionaudit:view');
        $audit = $this->createAudit();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/audits/{$audit->id}")
            ->assertStatus(200);
    }

    public function test_show_returns_404_for_nonexistent_audit(): void
    {
        $this->giveUserPermission($this->user, 'actionaudit:view');

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/audits/999999')
            ->assertStatus(404);
    }

    public function test_show_returns_403_without_permission(): void
    {
        $audit = $this->createAudit();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/audits/{$audit->id}")
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // Filters
    // -------------------------------------------------------------------------

    public function test_filter_by_action_type(): void
    {
        $this->giveUserPermission($this->user, 'actionaudit:view');
        $this->createAudit();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/audits?filter[action_type]=create')
            ->assertStatus(200);

        $types = collect($response->json('data'))->pluck('attributes.action_type');
        $this->assertTrue($types->every(fn ($t) => $t === 'create'));
    }

    public function test_filter_by_user_id(): void
    {
        $this->giveUserPermission($this->user, 'actionaudit:view');
        $this->createAudit();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/audits?filter[user_id]={$this->user->id}")
            ->assertStatus(200);

        $userIds = collect($response->json('data'))->pluck('attributes.user_id');
        $this->assertTrue($userIds->every(fn ($id) => $id == $this->user->id));
    }

    public function test_filter_by_entity_type(): void
    {
        $this->giveUserPermission($this->user, 'actionaudit:view');
        $this->createAudit();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/audits?filter[entity_type]=assets')
            ->assertStatus(200);

        $entityTypes = collect($response->json('data'))->pluck('attributes.entity_type');
        $this->assertTrue($entityTypes->every(fn ($t) => $t === 'assets'));
    }

    public function test_filter_by_description(): void
    {
        $this->giveUserPermission($this->user, 'actionaudit:view');
        $this->createAudit();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/audits?filter[description]=Test+audit')
            ->assertStatus(200);

        $descriptions = collect($response->json('data'))->pluck('attributes.description');
        $this->assertTrue($descriptions->every(fn ($d) => str_contains($d, 'Test audit')));
    }

    public function test_sort_by_created_at(): void
    {
        $this->giveUserPermission($this->user, 'actionaudit:view');
        $this->createAudit();

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/audits?sort=-created_at')
            ->assertStatus(200);
    }

    public function test_filter_by_org_id(): void
    {
        $this->giveUserPermission($this->user, 'actionaudit:view');
        $this->createAudit();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/audits?filter[org_id]={$this->org->id}")
            ->assertStatus(200);

        $orgIds = collect($response->json('data'))->pluck('attributes.org_id');
        $this->assertTrue($orgIds->every(fn ($id) => $id == $this->org->id));
    }

    public function test_filter_by_entity_id(): void
    {
        $this->giveUserPermission($this->user, 'actionaudit:view');
        $this->createAudit();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/audits?filter[entity_id]=1')
            ->assertStatus(200);

        $this->assertNotEmpty($response->json('data'));
    }

    public function test_filter_by_ip_address(): void
    {
        $this->giveUserPermission($this->user, 'actionaudit:view');
        $this->createAudit();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/audits?filter[ip_address]=127.0.0')
            ->assertStatus(200);

        $ips = collect($response->json('data'))->pluck('attributes.ip_address');
        $this->assertTrue($ips->every(fn ($ip) => str_contains($ip, '127.0.0')));
    }

    public function test_filter_by_user_agent(): void
    {
        $this->giveUserPermission($this->user, 'actionaudit:view');
        $this->createAudit();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/audits?filter[user_agent]=Test+Agent')
            ->assertStatus(200);

        $this->assertNotEmpty($response->json('data'));
    }
}
