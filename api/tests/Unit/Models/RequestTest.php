<?php

namespace Tests\Unit\Models;

use App\Enums\RequestScope;
use App\Enums\RequestStatus;
use App\Enums\RiskRating;
use App\Models\Asset;
use App\Models\AssetAccount;
use App\Models\Org;
use App\Models\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequestTest extends TestCase
{
    use RefreshDatabase;

    private Request $request;
    private Org $org;
    private Asset $asset;
    private AssetAccount $assetAccount;
    private User $requester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->org = Org::factory()->create();
        $this->asset = Asset::factory()->create(['org_id' => $this->org->id]);
        $this->assetAccount = AssetAccount::factory()->create(['asset_id' => $this->asset->id]);
        $this->requester = User::factory()->create();

        $startTime = now()->addHour();
        $this->request = Request::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'asset_account_id' => $this->assetAccount->id,
            'requester_id' => $this->requester->id,
            'start_datetime' => $startTime,
            'end_datetime' => $startTime->copy()->addMinutes(60),
            'duration' => 60,
            'reason' => 'Testing database access',
            'intended_query' => 'SELECT * FROM test_table',
            'scope' => RequestScope::READ_ONLY,
            'is_access_sensitive_data' => false,
            'status' => RequestStatus::PENDING,
        ]);
    }

    public function test_request_has_correct_fillable_attributes(): void
    {
        $expectedFillable = [
            'org_id',
            'asset_id',
            'asset_account_id',
            'requester_id',
            'start_datetime',
            'end_datetime',
            'duration',
            'reason',
            'intended_query',
            'scope',
            'is_access_sensitive_data',
            'sensitive_data_note',
            'approver_note',
            'approver_risk_rating',
            'status',
            'approved_by',
            'approved_at',
            'rejected_by',
            'rejected_at',
        ];

        $this->assertEquals($expectedFillable, $this->request->getFillable());
    }

    public function test_request_has_correct_casts(): void
    {
        $casts = $this->request->getCasts();

        $this->assertArrayHasKey('start_datetime', $casts);
        $this->assertEquals('datetime', $casts['start_datetime']);
        $this->assertArrayHasKey('end_datetime', $casts);
        $this->assertEquals('datetime', $casts['end_datetime']);
        $this->assertArrayHasKey('is_access_sensitive_data', $casts);
        $this->assertEquals('boolean', $casts['is_access_sensitive_data']);
        $this->assertArrayHasKey('status', $casts);
        $this->assertEquals(RequestStatus::class, $casts['status']);
        $this->assertArrayHasKey('approver_risk_rating', $casts);
        $this->assertEquals(RiskRating::class, $casts['approver_risk_rating']);
        $this->assertArrayHasKey('ai_risk_rating', $casts);
        $this->assertEquals(RiskRating::class, $casts['ai_risk_rating']);
        $this->assertArrayHasKey('scope', $casts);
        $this->assertEquals(RequestScope::class, $casts['scope']);
    }

    public function test_request_attribute_labels_are_defined(): void
    {
        $expectedLabels = [
            'org_id' => 'Organization',
            'asset_id' => 'Asset',
            'asset_account_id' => 'Account',
            'requester_id' => 'Requester',
            'start_datetime' => 'Start',
            'end_datetime' => 'End',
            'duration' => 'Duration',
            'reason' => 'Reason',
            'intended_query' => 'Intended Query',
            'scope' => 'Scope',
            'is_access_sensitive_data' => 'Is Access Sensitive Data',
            'sensitive_data_note' => 'Sensitive Data Note',
            'approver_note' => 'Approver Note',
            'approver_risk_rating' => 'Approver Risk Rating',
            'ai_note' => 'AI Note',
            'ai_risk_rating' => 'AI Risk Rating',
            'status' => 'Status',
            'approved_by' => 'Approved By',
            'approved_at' => 'Approved At',
            'rejected_by' => 'Rejected By',
            'rejected_at' => 'Rejected At',
        ];

        $this->assertEquals($expectedLabels, Request::$attributeLabels);
    }

    public function test_request_includable_relationships_are_defined(): void
    {
        $expectedIncludable = [
            'org',
            'asset',
            'assetAccount',
            'requester',
            'approver',
            'session',
            'audits',
            'createdBy',
            'updatedBy',
        ];

        $this->assertEquals($expectedIncludable, Request::$includable);
    }

    public function test_asset_relationship(): void
    {
        $asset = $this->request->asset;

        $this->assertInstanceOf(Asset::class, $asset);
        $this->assertEquals($this->asset->id, $asset->id);

        // Test the relationship type
        $relationship = $this->request->asset();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relationship);
        $this->assertEquals('App\Models\Asset', $relationship->getRelated()::class);
    }

    public function test_asset_account_relationship(): void
    {
        $assetAccount = $this->request->assetAccount;

        $this->assertInstanceOf(AssetAccount::class, $assetAccount);
        $this->assertEquals($this->assetAccount->id, $assetAccount->id);

        // Test the relationship type
        $relationship = $this->request->assetAccount();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relationship);
        $this->assertEquals('App\Models\AssetAccount', $relationship->getRelated()::class);
    }

    public function test_requester_relationship(): void
    {
        $requester = $this->request->requester;

        $this->assertInstanceOf(User::class, $requester);
        $this->assertEquals($this->requester->id, $requester->id);

        // Test the relationship type
        $relationship = $this->request->requester();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relationship);
        $this->assertEquals('App\Models\User', $relationship->getRelated()::class);
    }

    public function test_approver_relationship(): void
    {
        $approver = User::factory()->create();
        $this->request->update(['approved_by' => $approver->id]);

        $approverRelation = $this->request->fresh()->approver;

        $this->assertInstanceOf(User::class, $approverRelation);
        $this->assertEquals($approver->id, $approverRelation->id);

        // Test the relationship type
        $relationship = $this->request->approver();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relationship);
        $this->assertEquals('App\Models\User', $relationship->getRelated()::class);
    }

    public function test_session_relationship(): void
    {
        // Test the relationship type
        $relationship = $this->request->session();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class, $relationship);
        $this->assertEquals('App\Models\Session', $relationship->getRelated()::class);
    }

    public function test_audits_relationship(): void
    {
        // Test the relationship type
        $relationship = $this->request->audits();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relationship);
        $this->assertEquals('App\Models\SessionAudit', $relationship->getRelated()::class);
    }

    public function test_request_uses_correct_traits(): void
    {
        $traits = class_uses_recursive(Request::class);

        $this->assertContains('App\\Traits\\BelongsToOrganization', $traits);
        $this->assertContains('App\\Traits\\HasBlamable', $traits);
        $this->assertContains('Illuminate\\Database\\Eloquent\\Factories\\HasFactory', $traits);
    }

    public function test_request_extends_base_model(): void
    {
        $this->assertInstanceOf(\App\Models\Model::class, $this->request);
    }

    public function test_request_creation_with_all_attributes(): void
    {
        $startTime = now()->addHours(2);
        $approver = User::factory()->create();

        $request = Request::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'asset_account_id' => $this->assetAccount->id,
            'requester_id' => $this->requester->id,
            'start_datetime' => $startTime,
            'duration' => 120,
            'reason' => 'Complex database analysis',
            'intended_query' => 'SELECT COUNT(*) FROM sensitive_table WHERE date > ?',
            'scope' => RequestScope::READ_WRITE,
            'is_access_sensitive_data' => true,
            'sensitive_data_note' => 'Contains PII data',
            'approver_note' => 'Approved with conditions',
            'approver_risk_rating' => RiskRating::MEDIUM,
            'status' => RequestStatus::APPROVED,
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        $this->assertDatabaseHas('requests', [
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'asset_account_id' => $this->assetAccount->id,
            'requester_id' => $this->requester->id,
            'duration' => 120,
            'reason' => 'Complex database analysis',
            'scope' => RequestScope::READ_WRITE->value,
            'is_access_sensitive_data' => true,
            'status' => RequestStatus::APPROVED->value,
            'approved_by' => $approver->id,
        ]);

        // Test that enums and booleans are cast correctly
        $this->assertEquals(RequestScope::READ_WRITE, $request->scope);
        $this->assertEquals(RequestStatus::APPROVED, $request->status);
        $this->assertEquals(RiskRating::MEDIUM, $request->approver_risk_rating);
        $this->assertTrue($request->is_access_sensitive_data);
        $this->assertInstanceOf(RequestScope::class, $request->scope);
        $this->assertInstanceOf(RequestStatus::class, $request->status);
        $this->assertInstanceOf(RiskRating::class, $request->approver_risk_rating);
    }

    public function test_request_belongs_to_organization(): void
    {
        // Test through BelongsToOrganization trait
        $this->assertEquals($this->org->id, $this->request->org_id);

        // Test org relationship (from BelongsToOrganization trait)
        $this->assertInstanceOf(Org::class, $this->request->org);
        $this->assertEquals($this->org->id, $this->request->org->id);
    }

    public function test_request_status_enum_functionality(): void
    {
        // Test pending status
        $pendingRequest = Request::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->requester->id,
            'status' => RequestStatus::PENDING,
        ]);

        // Test approved status
        $approvedRequest = Request::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->requester->id,
            'status' => RequestStatus::APPROVED,
        ]);

        // Test rejected status
        $rejectedRequest = Request::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->requester->id,
            'status' => RequestStatus::REJECTED,
        ]);

        // Test expired status
        $expiredRequest = Request::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->requester->id,
            'status' => RequestStatus::EXPIRED,
        ]);

        $this->assertEquals(RequestStatus::PENDING, $pendingRequest->status);
        $this->assertEquals(RequestStatus::APPROVED, $approvedRequest->status);
        $this->assertEquals(RequestStatus::REJECTED, $rejectedRequest->status);
        $this->assertEquals(RequestStatus::EXPIRED, $expiredRequest->status);
    }

    public function test_request_scope_enum_functionality(): void
    {
        // Test different scope values
        $scopes = [
            RequestScope::READ_ONLY,
            RequestScope::READ_WRITE,
            RequestScope::DDL,
            RequestScope::DML,
            RequestScope::ALL,
        ];

        foreach ($scopes as $scope) {
            $request = Request::factory()->create([
                'org_id' => $this->org->id,
                'asset_id' => $this->asset->id,
                'requester_id' => $this->requester->id,
                'scope' => $scope,
            ]);

            $this->assertEquals($scope, $request->scope);
            $this->assertInstanceOf(RequestScope::class, $request->scope);
        }
    }

    public function test_request_risk_rating_enum_functionality(): void
    {
        $request = Request::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->requester->id,
            'approver_risk_rating' => RiskRating::HIGH,
            'ai_risk_rating' => RiskRating::LOW,
        ]);

        $this->assertEquals(RiskRating::HIGH, $request->approver_risk_rating);
        $this->assertEquals(RiskRating::LOW, $request->ai_risk_rating);
        $this->assertInstanceOf(RiskRating::class, $request->approver_risk_rating);
        $this->assertInstanceOf(RiskRating::class, $request->ai_risk_rating);
    }

    public function test_request_datetime_casting(): void
    {
        $startTime = Carbon::now()->addHours(3);
        $endTime = Carbon::now()->addHours(5);
        $approvedTime = Carbon::now()->subHour();

        $request = Request::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->requester->id,
            'start_datetime' => $startTime,
            'end_datetime' => $endTime,
            'approved_at' => $approvedTime,
        ]);

        $this->assertInstanceOf(Carbon::class, $request->start_datetime);
        $this->assertInstanceOf(Carbon::class, $request->end_datetime);
        $this->assertInstanceOf(Carbon::class, $request->approved_at);

        $this->assertEquals($startTime->format('Y-m-d H:i:s'), $request->start_datetime->format('Y-m-d H:i:s'));
        $this->assertEquals($endTime->format('Y-m-d H:i:s'), $request->end_datetime->format('Y-m-d H:i:s'));
        $this->assertEquals($approvedTime->format('Y-m-d H:i:s'), $request->approved_at->format('Y-m-d H:i:s'));
    }

    public function test_request_sensitive_data_functionality(): void
    {
        // Test request with sensitive data
        $sensitiveRequest = Request::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->requester->id,
            'is_access_sensitive_data' => true,
            'sensitive_data_note' => 'Contains customer PII',
        ]);

        // Test request without sensitive data
        $regularRequest = Request::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->requester->id,
            'is_access_sensitive_data' => false,
            'sensitive_data_note' => null,
        ]);

        $this->assertTrue($sensitiveRequest->is_access_sensitive_data);
        $this->assertEquals('Contains customer PII', $sensitiveRequest->sensitive_data_note);
        $this->assertFalse($regularRequest->is_access_sensitive_data);
        $this->assertNull($regularRequest->sensitive_data_note);
    }

    public function test_request_approval_workflow(): void
    {
        $approver = User::factory()->create();
        $approvedTime = now();

        // Test approval
        $this->request->update([
            'status' => RequestStatus::APPROVED,
            'approved_by' => $approver->id,
            'approved_at' => $approvedTime,
            'approver_note' => 'Approved after review',
            'approver_risk_rating' => RiskRating::LOW,
        ]);

        $this->assertEquals(RequestStatus::APPROVED, $this->request->fresh()->status);
        $this->assertEquals($approver->id, $this->request->fresh()->approved_by);
        $this->assertEquals('Approved after review', $this->request->fresh()->approver_note);
        $this->assertEquals(RiskRating::LOW, $this->request->fresh()->approver_risk_rating);
    }

    public function test_request_rejection_workflow(): void
    {
        $rejector = User::factory()->create();
        $rejectedTime = now();

        // Test rejection
        $this->request->update([
            'status' => RequestStatus::REJECTED,
            'rejected_by' => $rejector->id,
            'rejected_at' => $rejectedTime,
            'approver_note' => 'Rejected due to security concerns',
            'approver_risk_rating' => RiskRating::HIGH,
        ]);

        $this->assertEquals(RequestStatus::REJECTED, $this->request->fresh()->status);
        $this->assertEquals($rejector->id, $this->request->fresh()->rejected_by);
        $this->assertEquals('Rejected due to security concerns', $this->request->fresh()->approver_note);
        $this->assertEquals(RiskRating::HIGH, $this->request->fresh()->approver_risk_rating);
    }

    public function test_request_can_have_multiple_audits(): void
    {
        // Just test that the audits relationship is configured correctly
        $relationship = $this->request->audits();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relationship);
        $this->assertEquals('request_id', $relationship->getForeignKeyName());
        $this->assertEquals('App\Models\SessionAudit', $relationship->getRelated()::class);
    }
}
