<?php

namespace Tests\Unit\Controllers;

use App\Enums\SessionStatus;
use App\Http\Controllers\SessionApproverController;
use App\Http\Resources\Session\SessionResource;
use App\Models\Asset;
use App\Models\Org;
use App\Models\Request as RequestModel;
use App\Models\Session;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionApproverControllerTest extends TestCase
{
    use RefreshDatabase;

    private SessionApproverController $controller;
    private User $user;
    private User $approver;
    private Org $org;
    private Asset $asset;
    private RequestModel $request;
    private Session $session;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new SessionApproverController;
        $this->org = Org::factory()->create();
        $this->user = User::factory()->create();
        $this->approver = User::factory()->create();
        $this->asset = Asset::factory()->create(['org_id' => $this->org->id]);
        $this->request = RequestModel::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->user->id,
            'approved_by' => $this->approver->id,
        ]);
        $this->session = Session::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'request_id' => $this->request->id,
            'requester_id' => $this->user->id,
            'approver_id' => $this->approver->id,
            'status' => SessionStatus::STARTED,
        ]);
    }

    public function test_controller_extends_base_controller(): void
    {
        $this->assertInstanceOf(\App\Http\Controllers\Controller::class, $this->controller);
    }

    public function test_controller_has_required_methods(): void
    {
        $this->assertTrue(method_exists($this->controller, 'delete'));
    }

    public function test_delete_terminates_session_and_returns_resource(): void
    {
        // Mock the authorize method to return true
        $this->controller = $this->createPartialMock(SessionApproverController::class, ['authorize']);
        $this->controller->expects($this->once())
            ->method('authorize')
            ->with('terminate', $this->session)
            ->willReturn(true);

        $response = $this->controller->delete($this->session);

        $this->assertInstanceOf(SessionResource::class, $response);
        $this->assertEquals(SessionStatus::TERMINATED, $this->session->fresh()->status);
    }

    public function test_delete_with_different_session_states(): void
    {
        // Test with scheduled session (should not be terminatable)
        $scheduledSession = Session::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'request_id' => $this->request->id,
            'requester_id' => $this->user->id,
            'approver_id' => $this->approver->id,
            'status' => SessionStatus::SCHEDULED,
        ]);

        // Mock the authorize method to return true
        $this->controller = $this->createPartialMock(SessionApproverController::class, ['authorize']);
        $this->controller->expects($this->once())
            ->method('authorize')
            ->with('terminate', $scheduledSession)
            ->willReturn(true);

        $response = $this->controller->delete($scheduledSession);

        $this->assertInstanceOf(SessionResource::class, $response);
        // Session should still be scheduled since it can't be terminated
        $this->assertEquals(SessionStatus::SCHEDULED, $scheduledSession->fresh()->status);
    }

    public function test_delete_with_ended_session(): void
    {
        // Test with ended session (should not be terminatable)
        $endedSession = Session::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'request_id' => $this->request->id,
            'requester_id' => $this->user->id,
            'approver_id' => $this->approver->id,
            'status' => SessionStatus::ENDED,
        ]);

        // Mock the authorize method to return true
        $this->controller = $this->createPartialMock(SessionApproverController::class, ['authorize']);
        $this->controller->expects($this->once())
            ->method('authorize')
            ->with('terminate', $endedSession)
            ->willReturn(true);

        $response = $this->controller->delete($endedSession);

        $this->assertInstanceOf(SessionResource::class, $response);
        // Session should still be ended since it can't be terminated
        $this->assertEquals(SessionStatus::ENDED, $endedSession->fresh()->status);
    }

    public function test_delete_with_cancelled_session(): void
    {
        // Test with cancelled session (should not be terminatable)
        $cancelledSession = Session::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'request_id' => $this->request->id,
            'requester_id' => $this->user->id,
            'approver_id' => $this->approver->id,
            'status' => SessionStatus::CANCELLED,
        ]);

        // Mock the authorize method to return true
        $this->controller = $this->createPartialMock(SessionApproverController::class, ['authorize']);
        $this->controller->expects($this->once())
            ->method('authorize')
            ->with('terminate', $cancelledSession)
            ->willReturn(true);

        $response = $this->controller->delete($cancelledSession);

        $this->assertInstanceOf(SessionResource::class, $response);
        // Session should still be cancelled since it can't be terminated
        $this->assertEquals(SessionStatus::CANCELLED, $cancelledSession->fresh()->status);
    }
}
