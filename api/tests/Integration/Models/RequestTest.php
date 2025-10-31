<?php

namespace Tests\Unit\Models;

use App\Enums\DatabaseScope;
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
            'scope' => DatabaseScope::READ_ONLY,
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
            'databases',
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
            'ai_note',
            'ai_risk_rating',
            'status',
            'submitted_at',
            'approved_by',
            'approved_at',
            'rejected_by',
            'rejected_at',
            'cancelled_by',
            'cancelled_at',
            'expired_at',
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
        $this->assertEquals(DatabaseScope::class, $casts['scope']);
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
            'submitted_at' => 'Submitted At',
            'cancelled_by' => 'Cancelled By',
            'cancelled_at' => 'Cancelled At',
            'expired_at' => 'Expired At',
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
            'rejecter',
            'session',
            'audits',
            'createdBy',
            'updatedBy',
            'cancelledBy',
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

        // Create request without events to avoid RequestCreated listener trying to submit an APPROVED request
        Request::unsetEventDispatcher();

        $request = Request::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'asset_account_id' => $this->assetAccount->id,
            'requester_id' => $this->requester->id,
            'start_datetime' => $startTime,
            'duration' => 120,
            'reason' => 'Complex database analysis',
            'intended_query' => 'SELECT COUNT(*) FROM sensitive_table WHERE date > ?',
            'scope' => DatabaseScope::READ_WRITE,
            'is_access_sensitive_data' => true,
            'sensitive_data_note' => 'Contains PII data',
            'approver_note' => 'Approved with conditions',
            'approver_risk_rating' => RiskRating::MEDIUM,
            'status' => RequestStatus::APPROVED,
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        // Re-set event dispatcher for subsequent tests
        Request::setEventDispatcher($this->app['events']);

        $this->assertDatabaseHas('requests', [
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'asset_account_id' => $this->assetAccount->id,
            'requester_id' => $this->requester->id,
            'duration' => 120,
            'reason' => 'Complex database analysis',
            'scope' => DatabaseScope::READ_WRITE->value,
            'is_access_sensitive_data' => true,
            'status' => RequestStatus::APPROVED->value,
            'approved_by' => $approver->id,
        ]);

        // Test that enums and booleans are cast correctly
        $this->assertEquals(DatabaseScope::READ_WRITE, $request->scope);
        $this->assertEquals(RequestStatus::APPROVED, $request->status);
        $this->assertEquals(RiskRating::MEDIUM, $request->approver_risk_rating);
        $this->assertTrue($request->is_access_sensitive_data);
        $this->assertInstanceOf(DatabaseScope::class, $request->scope);
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
        // Test all statuses without creating multiple database records
        $statuses = [
            RequestStatus::PENDING,
            RequestStatus::APPROVED,
            RequestStatus::REJECTED,
            RequestStatus::EXPIRED,
        ];

        foreach ($statuses as $status) {
            // Update existing request instead of creating new ones
            $this->request->status = $status;
            $this->assertEquals($status, $this->request->status);
            $this->assertInstanceOf(RequestStatus::class, $this->request->status);
        }

        // Verify database persistence with one write
        $this->request->update(['status' => RequestStatus::APPROVED]);
        $this->assertEquals(RequestStatus::APPROVED, $this->request->fresh()->status);
    }

    public function test_request_scope_enum_functionality(): void
    {
        // Test different scope values without creating multiple database records
        $scopes = [
            DatabaseScope::READ_ONLY,
            DatabaseScope::READ_WRITE,
            DatabaseScope::DDL,
            DatabaseScope::DML,
            DatabaseScope::ALL,
        ];

        foreach ($scopes as $scope) {
            // Update existing request instead of creating new ones
            $this->request->scope = $scope;
            $this->assertEquals($scope, $this->request->scope);
            $this->assertInstanceOf(DatabaseScope::class, $this->request->scope);
        }

        // Verify database persistence with one write
        $this->request->update(['scope' => DatabaseScope::READ_WRITE]);
        $this->assertEquals(DatabaseScope::READ_WRITE, $this->request->fresh()->scope);
    }

    public function test_request_risk_rating_enum_functionality(): void
    {
        // Test enum casting without creating new database record
        $this->request->approver_risk_rating = RiskRating::HIGH;
        $this->request->ai_risk_rating = RiskRating::LOW;

        $this->assertEquals(RiskRating::HIGH, $this->request->approver_risk_rating);
        $this->assertEquals(RiskRating::LOW, $this->request->ai_risk_rating);
        $this->assertInstanceOf(RiskRating::class, $this->request->approver_risk_rating);
        $this->assertInstanceOf(RiskRating::class, $this->request->ai_risk_rating);

        // Verify database persistence
        $this->request->update([
            'approver_risk_rating' => RiskRating::HIGH,
            'ai_risk_rating' => RiskRating::LOW,
        ]);
        $this->assertEquals(RiskRating::HIGH, $this->request->fresh()->approver_risk_rating);
        $this->assertEquals(RiskRating::LOW, $this->request->fresh()->ai_risk_rating);
    }

    public function test_request_datetime_casting(): void
    {
        $startTime = Carbon::now()->addHours(3);
        $endTime = Carbon::now()->addHours(5);
        $approvedTime = Carbon::now()->subHour();

        // Test datetime casting without creating new database record
        $this->request->start_datetime = $startTime;
        $this->request->end_datetime = $endTime;
        $this->request->approved_at = $approvedTime;

        $this->assertInstanceOf(Carbon::class, $this->request->start_datetime);
        $this->assertInstanceOf(Carbon::class, $this->request->end_datetime);
        $this->assertInstanceOf(Carbon::class, $this->request->approved_at);

        $this->assertEquals($startTime->format('Y-m-d H:i:s'), $this->request->start_datetime->format('Y-m-d H:i:s'));
        $this->assertEquals($endTime->format('Y-m-d H:i:s'), $this->request->end_datetime->format('Y-m-d H:i:s'));
        $this->assertEquals($approvedTime->format('Y-m-d H:i:s'), $this->request->approved_at->format('Y-m-d H:i:s'));

        // Verify database persistence
        $this->request->update([
            'start_datetime' => $startTime,
            'end_datetime' => $endTime,
            'approved_at' => $approvedTime,
        ]);
        $freshRequest = $this->request->fresh();
        $this->assertEquals($startTime->format('Y-m-d H:i:s'), $freshRequest->start_datetime->format('Y-m-d H:i:s'));
        $this->assertEquals($endTime->format('Y-m-d H:i:s'), $freshRequest->end_datetime->format('Y-m-d H:i:s'));
        $this->assertEquals($approvedTime->format('Y-m-d H:i:s'), $freshRequest->approved_at->format('Y-m-d H:i:s'));
    }

    public function test_request_sensitive_data_functionality(): void
    {
        // Test request with sensitive data using existing request
        $this->request->is_access_sensitive_data = true;
        $this->request->sensitive_data_note = 'Contains customer PII';

        $this->assertTrue($this->request->is_access_sensitive_data);
        $this->assertEquals('Contains customer PII', $this->request->sensitive_data_note);

        // Test request without sensitive data
        $this->request->is_access_sensitive_data = false;
        $this->request->sensitive_data_note = null;

        $this->assertFalse($this->request->is_access_sensitive_data);
        $this->assertNull($this->request->sensitive_data_note);

        // Verify database persistence
        $this->request->update([
            'is_access_sensitive_data' => true,
            'sensitive_data_note' => 'Contains customer PII',
        ]);
        $freshRequest = $this->request->fresh();
        $this->assertTrue($freshRequest->is_access_sensitive_data);
        $this->assertEquals('Contains customer PII', $freshRequest->sensitive_data_note);
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
