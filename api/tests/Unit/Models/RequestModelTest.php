<?php

namespace Tests\Unit\Models;

use App\Enums\RequestStatus;
use App\Models\Asset;
use App\Models\Org;
use App\Models\Request as RequestModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RequestModelTest extends TestCase
{
    use RefreshDatabase;

    private Org $org;
    private Asset $asset;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        $this->org = Org::factory()->create();
        $this->user = User::factory()->create();
        $this->org->users()->attach($this->user->id);
        $this->asset = Asset::factory()->create(['org_id' => $this->org->id]);
        Auth::login($this->user);
    }

    private function makeRequest(array $overrides = []): RequestModel
    {
        return RequestModel::factory()->create(array_merge([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->user->id,
            'status' => RequestStatus::SUBMITTED,
            'start_datetime' => now()->subHour(),
            'end_datetime' => now()->addHour(),
        ], $overrides));
    }

    // -------------------------------------------------------------------------
    // canApprove
    // -------------------------------------------------------------------------

    public function test_can_approve_returns_true_when_submitted_with_future_end(): void
    {
        $request = $this->makeRequest([
            'status' => RequestStatus::SUBMITTED,
            'end_datetime' => now()->addHour(),
        ]);
        $this->assertTrue($request->canApprove());
    }

    public function test_can_approve_returns_false_when_submitted_with_past_end(): void
    {
        $request = $this->makeRequest([
            'status' => RequestStatus::SUBMITTED,
            'end_datetime' => now()->subHour(),
        ]);
        $this->assertFalse($request->canApprove());
    }

    public function test_can_approve_returns_false_when_pending(): void
    {
        $request = $this->makeRequest([
            'status' => RequestStatus::PENDING,
            'end_datetime' => now()->addHour(),
        ]);
        $this->assertFalse($request->canApprove());
    }

    // -------------------------------------------------------------------------
    // canCancel (mirrors canApprove)
    // -------------------------------------------------------------------------

    public function test_can_cancel_returns_true_when_submitted_with_future_end(): void
    {
        $request = $this->makeRequest([
            'status' => RequestStatus::SUBMITTED,
            'end_datetime' => now()->addHour(),
        ]);
        $this->assertTrue($request->canCancel());
    }

    public function test_can_cancel_returns_false_when_submitted_with_past_end(): void
    {
        $request = $this->makeRequest([
            'status' => RequestStatus::SUBMITTED,
            'end_datetime' => now()->subHour(),
        ]);
        $this->assertFalse($request->canCancel());
    }

    // -------------------------------------------------------------------------
    // canExpire
    // -------------------------------------------------------------------------

    public function test_can_expire_returns_true_when_submitted_with_past_start(): void
    {
        $request = $this->makeRequest([
            'status' => RequestStatus::SUBMITTED,
            'start_datetime' => now()->subHour(),
        ]);
        $this->assertTrue($request->canExpire());
    }

    public function test_can_expire_returns_true_when_pending_with_past_start(): void
    {
        $request = $this->makeRequest([
            'status' => RequestStatus::PENDING,
            'start_datetime' => now()->subHour(),
        ]);
        $this->assertTrue($request->canExpire());
    }

    public function test_can_expire_returns_false_when_submitted_with_future_start(): void
    {
        $request = $this->makeRequest([
            'status' => RequestStatus::SUBMITTED,
            'start_datetime' => now()->addHour(),
        ]);
        $this->assertFalse($request->canExpire());
    }

    // -------------------------------------------------------------------------
    // submit()
    // -------------------------------------------------------------------------

    public function test_submit_transitions_pending_request_to_submitted(): void
    {
        $request = $this->makeRequest(['status' => RequestStatus::PENDING]);
        $request->submit();
        $this->assertEquals(RequestStatus::SUBMITTED, $request->fresh()->status);
        $this->assertNotNull($request->fresh()->submitted_at);
    }

    public function test_submit_throws_when_not_pending(): void
    {
        $request = $this->makeRequest(['status' => RequestStatus::SUBMITTED]);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Request is not pending');
        $request->submit();
    }

    // -------------------------------------------------------------------------
    // approve()
    // -------------------------------------------------------------------------

    public function test_approve_transitions_submitted_request_to_approved(): void
    {
        $request = $this->makeRequest([
            'status' => RequestStatus::SUBMITTED,
            'end_datetime' => now()->addHour(),
        ]);
        $request->approve();
        $this->assertEquals(RequestStatus::APPROVED, $request->fresh()->status);
        $this->assertNotNull($request->fresh()->approved_at);
        $this->assertEquals($this->user->id, $request->fresh()->approved_by);
    }

    public function test_approve_throws_when_not_eligible(): void
    {
        $request = $this->makeRequest([
            'status' => RequestStatus::SUBMITTED,
            'end_datetime' => now()->subHour(),
        ]);
        $this->expectException(\Exception::class);
        $request->approve();
    }

    // -------------------------------------------------------------------------
    // reject()
    // -------------------------------------------------------------------------

    public function test_reject_transitions_submitted_request_to_rejected(): void
    {
        $request = $this->makeRequest([
            'status' => RequestStatus::SUBMITTED,
            'end_datetime' => now()->addHour(),
        ]);
        $request->reject();
        $this->assertEquals(RequestStatus::REJECTED, $request->fresh()->status);
        $this->assertNotNull($request->fresh()->rejected_at);
    }

    // -------------------------------------------------------------------------
    // cancel()
    // -------------------------------------------------------------------------

    public function test_cancel_transitions_submitted_request_to_cancelled(): void
    {
        $request = $this->makeRequest([
            'status' => RequestStatus::SUBMITTED,
            'end_datetime' => now()->addHour(),
        ]);
        $request->cancel();
        $this->assertEquals(RequestStatus::CANCELLED, $request->fresh()->status);
        $this->assertNotNull($request->fresh()->cancelled_at);
    }

    public function test_cancel_throws_when_not_eligible(): void
    {
        $request = $this->makeRequest([
            'status' => RequestStatus::SUBMITTED,
            'end_datetime' => now()->subHour(),
        ]);
        $this->expectException(\Exception::class);
        $request->cancel();
    }
}
