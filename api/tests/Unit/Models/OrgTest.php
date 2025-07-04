<?php

namespace Tests\Unit\Models;

use App\Enums\Status;
use App\Models\ActionAudit;
use App\Models\Asset;
use App\Models\Org;
use App\Models\Request;
use App\Models\Session;
use App\Models\SessionAudit;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrgTest extends TestCase
{
    use RefreshDatabase;

    private Org $org;

    protected function setUp(): void
    {
        parent::setUp();

        $this->org = Org::factory()->create();
    }

    public function test_org_has_correct_fillable_attributes(): void
    {
        $expectedFillable = [
            'name',
            'description',
            'status',
        ];

        $this->assertEquals($expectedFillable, $this->org->getFillable());
    }

    public function test_org_has_correct_casts(): void
    {
        $casts = $this->org->getCasts();

        $this->assertArrayHasKey('status', $casts);
        $this->assertArrayHasKey('created_at', $casts);
        $this->assertArrayHasKey('updated_at', $casts);
        $this->assertArrayHasKey('deleted_at', $casts);
        $this->assertEquals(Status::class, $casts['status']);
    }

    public function test_org_belongs_to_many_users(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        // Attach users to org
        $this->org->users()->attach([$user1->id, $user2->id]);

        $users = $this->org->users;

        $this->assertCount(2, $users);
        $this->assertTrue($users->contains($user1));
        $this->assertTrue($users->contains($user2));
        $this->assertFalse($users->contains($user3));
    }

    public function test_org_has_many_user_groups(): void
    {
        $group1 = UserGroup::factory()->create(['org_id' => $this->org->id]);
        $group2 = UserGroup::factory()->create(['org_id' => $this->org->id]);
        UserGroup::factory()->create(); // Different org

        $groups = $this->org->userGroups;

        $this->assertCount(2, $groups);
        $this->assertTrue($groups->contains($group1));
        $this->assertTrue($groups->contains($group2));
    }

    public function test_org_has_many_assets(): void
    {
        $asset1 = Asset::factory()->create(['org_id' => $this->org->id]);
        $asset2 = Asset::factory()->create(['org_id' => $this->org->id]);
        Asset::factory()->create(); // Different org

        $assets = $this->org->assets;

        $this->assertCount(2, $assets);
        $this->assertTrue($assets->contains($asset1));
        $this->assertTrue($assets->contains($asset2));
    }

    public function test_org_has_many_requests(): void
    {
        $asset1 = Asset::factory()->create(['org_id' => $this->org->id]);
        $asset2 = Asset::factory()->create(['org_id' => $this->org->id]);
        $requester = User::factory()->create();

        $request1 = Request::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $asset1->id,
            'requester_id' => $requester->id,
            'start_datetime' => now(),
            'end_datetime' => now()->addHours(2),
            'duration' => 120,
            'reason' => 'Test request',
        ]);
        $request2 = Request::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $asset2->id,
            'requester_id' => $requester->id,
            'start_datetime' => now(),
            'end_datetime' => now()->addHours(2),
            'duration' => 120,
            'reason' => 'Test request',
        ]);

        $otherOrg = Org::factory()->create();
        $otherAsset = Asset::factory()->create(['org_id' => $otherOrg->id]);
        Request::factory()->create([
            'org_id' => $otherOrg->id,
            'asset_id' => $otherAsset->id,
            'requester_id' => $requester->id,
            'start_datetime' => now(),
            'end_datetime' => now()->addHours(2),
            'duration' => 120,
            'reason' => 'Test request',
        ]); // Different org

        $requests = $this->org->requests;

        $this->assertCount(2, $requests);
        $this->assertTrue($requests->contains($request1));
        $this->assertTrue($requests->contains($request2));
    }

    public function test_org_has_many_sessions(): void
    {
        $asset1 = Asset::factory()->create(['org_id' => $this->org->id]);
        $asset2 = Asset::factory()->create(['org_id' => $this->org->id]);
        $requester = User::factory()->create();

        $request1 = Request::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $asset1->id,
            'requester_id' => $requester->id,
            'start_datetime' => now(),
            'end_datetime' => now()->addHours(2),
            'duration' => 120,
            'reason' => 'Test request',
        ]);
        $request2 = Request::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $asset2->id,
            'requester_id' => $requester->id,
            'start_datetime' => now(),
            'end_datetime' => now()->addHours(2),
            'duration' => 120,
            'reason' => 'Test request',
        ]);

        $approver = User::factory()->create();

        $session1 = Session::factory()->create([
            'org_id' => $this->org->id,
            'request_id' => $request1->id,
            'asset_id' => $asset1->id,
            'requester_id' => $requester->id,
            'approver_id' => $approver->id,
            'start_datetime' => now(),
            'scheduled_end_datetime' => now()->addHours(2),
            'requested_duration' => 120,
        ]);
        $session2 = Session::factory()->create([
            'org_id' => $this->org->id,
            'request_id' => $request2->id,
            'asset_id' => $asset2->id,
            'requester_id' => $requester->id,
            'approver_id' => $approver->id,
            'start_datetime' => now(),
            'scheduled_end_datetime' => now()->addHours(2),
            'requested_duration' => 120,
        ]);

        $otherOrg = Org::factory()->create();
        $otherAsset = Asset::factory()->create(['org_id' => $otherOrg->id]);
        $otherRequest = Request::factory()->create([
            'org_id' => $otherOrg->id,
            'asset_id' => $otherAsset->id,
            'requester_id' => $requester->id,
            'start_datetime' => now(),
            'end_datetime' => now()->addHours(2),
            'duration' => 120,
            'reason' => 'Test request',
        ]);
        Session::factory()->create([
            'org_id' => $otherOrg->id,
            'request_id' => $otherRequest->id,
            'asset_id' => $otherAsset->id,
            'requester_id' => $requester->id,
            'approver_id' => $approver->id,
            'start_datetime' => now(),
            'scheduled_end_datetime' => now()->addHours(2),
            'requested_duration' => 120,
        ]); // Different org

        $sessions = $this->org->sessions;

        $this->assertCount(2, $sessions);
        $this->assertTrue($sessions->contains($session1));
        $this->assertTrue($sessions->contains($session2));
    }

    public function test_org_has_many_session_audits(): void
    {
        $asset1 = Asset::factory()->create(['org_id' => $this->org->id]);
        $requester = User::factory()->create();
        $approver = User::factory()->create();

        $request1 = Request::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $asset1->id,
            'requester_id' => $requester->id,
            'start_datetime' => now(),
            'end_datetime' => now()->addHours(2),
            'duration' => 120,
            'reason' => 'Test request for audit',
        ]);

        $session1 = Session::factory()->create([
            'org_id' => $this->org->id,
            'request_id' => $request1->id,
            'asset_id' => $asset1->id,
            'requester_id' => $requester->id,
            'approver_id' => $approver->id,
            'start_datetime' => now(),
            'scheduled_end_datetime' => now()->addHours(2),
            'requested_duration' => 120,
        ]);

        $audit1 = SessionAudit::factory()->create([
            'org_id' => $this->org->id,
            'session_id' => $session1->id,
            'request_id' => $request1->id,
            'asset_id' => $asset1->id,
            'user_id' => $requester->id,
            'query_text' => 'SELECT * FROM test_table',
            'query_timestamp' => now(),
        ]);

        $audit2 = SessionAudit::factory()->create([
            'org_id' => $this->org->id,
            'session_id' => $session1->id,
            'request_id' => $request1->id,
            'asset_id' => $asset1->id,
            'user_id' => $requester->id,
            'query_text' => 'SELECT count(*) FROM users',
            'query_timestamp' => now(),
        ]);

        // Different org
        $otherOrg = Org::factory()->create();
        $otherAsset = Asset::factory()->create(['org_id' => $otherOrg->id]);
        $otherRequest = Request::factory()->create([
            'org_id' => $otherOrg->id,
            'asset_id' => $otherAsset->id,
            'requester_id' => $requester->id,
            'start_datetime' => now(),
            'end_datetime' => now()->addHours(2),
            'duration' => 120,
            'reason' => 'Other org request',
        ]);
        $otherSession = Session::factory()->create([
            'org_id' => $otherOrg->id,
            'request_id' => $otherRequest->id,
            'asset_id' => $otherAsset->id,
            'requester_id' => $requester->id,
            'approver_id' => $approver->id,
            'start_datetime' => now(),
            'scheduled_end_datetime' => now()->addHours(2),
            'requested_duration' => 120,
        ]);
        SessionAudit::factory()->create([
            'org_id' => $otherOrg->id,
            'session_id' => $otherSession->id,
            'request_id' => $otherRequest->id,
            'asset_id' => $otherAsset->id,
            'user_id' => $requester->id,
            'query_text' => 'SELECT * FROM other_table',
            'query_timestamp' => now(),
        ]);

        $audits = $this->org->sessionAudits;

        $this->assertCount(2, $audits);
        $this->assertTrue($audits->contains($audit1));
        $this->assertTrue($audits->contains($audit2));
    }

    public function test_org_has_many_action_audits(): void
    {
        // Create ActionAudits manually since factory has issues
        $user = User::factory()->create();

        $audit1 = new ActionAudit;
        $audit1->org_id = $this->org->id;
        $audit1->user_id = $user->id;
        $audit1->action_type = \App\Enums\AuditAction::CREATE;
        $audit1->entity_type = 'test_entity';
        $audit1->entity_id = 1;
        $audit1->description = 'Test audit';
        $audit1->timestamps = false;
        $audit1->save();

        $audit2 = new ActionAudit;
        $audit2->org_id = $this->org->id;
        $audit2->user_id = $user->id;
        $audit2->action_type = \App\Enums\AuditAction::UPDATE;
        $audit2->entity_type = 'test_entity_2';
        $audit2->entity_id = 2;
        $audit2->description = 'Test audit 2';
        $audit2->timestamps = false;
        $audit2->save();

        $otherOrg = Org::factory()->create();
        $audit3 = new ActionAudit;
        $audit3->org_id = $otherOrg->id;
        $audit3->user_id = $user->id;
        $audit3->action_type = \App\Enums\AuditAction::DELETE;
        $audit3->entity_type = 'other_entity';
        $audit3->entity_id = 3;
        $audit3->description = 'Test audit 3';
        $audit3->timestamps = false;
        $audit3->save();

        $audits = $this->org->actionAudits;

        $this->assertCount(2, $audits);
        $this->assertTrue($audits->contains($audit1));
        $this->assertTrue($audits->contains($audit2));
        $this->assertFalse($audits->contains($audit3));
    }

    public function test_org_soft_deletes(): void
    {
        $this->assertFalse($this->org->trashed());

        $this->org->delete();

        $this->assertTrue($this->org->fresh()->trashed());
        $this->assertNotNull($this->org->fresh()->deleted_at);
    }

    public function test_org_uses_traits(): void
    {
        $traits = class_uses_recursive(Org::class);

        $this->assertContains('App\Traits\HasBlamable', $traits);
        $this->assertContains('Illuminate\Database\Eloquent\Factories\HasFactory', $traits);
        $this->assertContains('Illuminate\Database\Eloquent\SoftDeletes', $traits);
    }

    public function test_org_attribute_labels_are_defined(): void
    {
        $expectedLabels = [
            'name' => 'Name',
            'description' => 'Description',
            'status' => 'Status',
        ];

        $this->assertEquals($expectedLabels, Org::$attributeLabels);
    }

    public function test_org_includable_relationships_are_defined(): void
    {
        $expectedIncludable = [
            'users',
            'userGroups',
            'assets',
            'requests',
            'sessions',
            'sessionAudits',
            'actionAudits',
            'createdBy',
            'updatedBy',
        ];

        $this->assertEquals($expectedIncludable, Org::$includable);
    }

    public function test_org_can_be_created_with_valid_data(): void
    {
        $orgData = [
            'name' => 'Test Organization',
            'description' => 'A test organization',
            'status' => Status::ACTIVE,
        ];

        $org = Org::create($orgData);

        $this->assertInstanceOf(Org::class, $org);
        $this->assertEquals('Test Organization', $org->name);
        $this->assertEquals('A test organization', $org->description);
        $this->assertEquals(Status::ACTIVE, $org->status);
    }

    public function test_org_status_enum_casting(): void
    {
        $org = Org::factory()->create([
            'status' => Status::ACTIVE,
        ]);

        $this->assertInstanceOf(Status::class, $org->status);
        $this->assertEquals(Status::ACTIVE, $org->status);
    }

    public function test_org_timestamps_are_cast_to_datetime(): void
    {
        $this->assertInstanceOf(\DateTime::class, $this->org->created_at);
        $this->assertInstanceOf(\DateTime::class, $this->org->updated_at);
    }

    public function test_org_can_have_users_attached_and_detached(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Initially no users
        $this->assertCount(0, $this->org->users);

        // Attach users
        $this->org->users()->attach([$user1->id, $user2->id]);
        $this->assertCount(2, $this->org->fresh()->users);

        // Detach one user
        $this->org->users()->detach($user1->id);
        $this->assertCount(1, $this->org->fresh()->users);
        $this->assertTrue($this->org->fresh()->users->contains($user2));
        $this->assertFalse($this->org->fresh()->users->contains($user1));
    }
}
