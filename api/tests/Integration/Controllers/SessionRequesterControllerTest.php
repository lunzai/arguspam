<?php

namespace Tests\Integration\Controllers;

use App\Enums\SessionStatus;
use App\Http\Controllers\SessionRequesterController;
use App\Http\Resources\Session\SessionResource;
use App\Models\Asset;
use App\Models\Org;
use App\Models\Request as RequestModel;
use App\Models\Session;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class SessionRequesterControllerTest extends TestCase
{
    use RefreshDatabase;

    private SessionRequesterController $controller;
    private User $user;
    private Org $org;
    private Asset $asset;
    private RequestModel $request;
    private Session $session;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new SessionRequesterController;
        $this->org = Org::factory()->create();
        $this->user = User::factory()->create();
        $this->asset = Asset::factory()->create(['org_id' => $this->org->id]);
        $this->request = RequestModel::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->user->id,
        ]);
        $this->session = Session::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'request_id' => $this->request->id,
            'requester_id' => $this->user->id,
            'status' => SessionStatus::SCHEDULED,
            'scheduled_start_datetime' => now()->subHour(),
            'scheduled_end_datetime' => now()->addHour(),
        ]);
    }

    public function test_controller_extends_base_controller(): void
    {
        $this->assertInstanceOf(\App\Http\Controllers\Controller::class, $this->controller);
    }

    public function test_controller_has_required_methods(): void
    {
        $this->assertTrue(method_exists($this->controller, 'show'));
        $this->assertTrue(method_exists($this->controller, 'store'));
        $this->assertTrue(method_exists($this->controller, 'update'));
        $this->assertTrue(method_exists($this->controller, 'delete'));
    }

    public function test_show_returns_correct_data_for_owner(): void
    {
        Auth::login($this->user);

        // Mock the authorize method to return true
        $this->controller = $this->createPartialMock(SessionRequesterController::class, ['authorize']);
        $this->controller->expects($this->once())
            ->method('authorize')
            ->with('view', $this->session)
            ->willReturn(true);

        $response = $this->controller->show($this->session);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('canEnd', $response['data']);
        $this->assertArrayHasKey('canStart', $response['data']);
        $this->assertArrayHasKey('canCancel', $response['data']);

        // Since session is scheduled and within time window, should be able to start
        $this->assertTrue($response['data']['canStart']);
        $this->assertFalse($response['data']['canEnd']); // Not started yet
        $this->assertTrue($response['data']['canCancel']); // Can cancel scheduled session
    }

    public function test_show_returns_false_for_non_owner(): void
    {
        $otherUser = User::factory()->create();
        Auth::login($otherUser);

        // Mock the authorize method to return true
        $this->controller = $this->createPartialMock(SessionRequesterController::class, ['authorize']);
        $this->controller->expects($this->once())
            ->method('authorize')
            ->with('view', $this->session)
            ->willReturn(true);

        $response = $this->controller->show($this->session);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('data', $response);
        $this->assertFalse($response['data']['canEnd']);
        $this->assertFalse($response['data']['canStart']);
        $this->assertFalse($response['data']['canCancel']);
    }

    public function test_store_starts_session_and_returns_resource(): void
    {
        Auth::login($this->user);

        // Mock the authorize method to return true
        $this->controller = $this->createPartialMock(SessionRequesterController::class, ['authorize']);
        $this->controller->expects($this->once())
            ->method('authorize')
            ->with('start', $this->session)
            ->willReturn(true);

        $response = $this->controller->store($this->session);

        $this->assertInstanceOf(SessionResource::class, $response);
        $this->assertEquals(SessionStatus::STARTED, $this->session->fresh()->status);
    }

    public function test_update_ends_session_and_returns_resource(): void
    {
        // First start the session
        $this->session->update(['status' => SessionStatus::STARTED]);
        Auth::login($this->user);

        // Mock the authorize method to return true
        $this->controller = $this->createPartialMock(SessionRequesterController::class, ['authorize']);
        $this->controller->expects($this->once())
            ->method('authorize')
            ->with('end', $this->session)
            ->willReturn(true);

        $response = $this->controller->update($this->session);

        $this->assertInstanceOf(SessionResource::class, $response);
        $this->assertEquals(SessionStatus::ENDED, $this->session->fresh()->status);
    }

    public function test_delete_cancels_session_and_returns_resource(): void
    {
        Auth::login($this->user);

        // Mock the authorize method to return true
        $this->controller = $this->createPartialMock(SessionRequesterController::class, ['authorize']);
        $this->controller->expects($this->once())
            ->method('authorize')
            ->with('cancel', $this->session)
            ->willReturn(true);

        $response = $this->controller->delete($this->session);

        $this->assertInstanceOf(SessionResource::class, $response);
        $this->assertEquals(SessionStatus::CANCELLED, $this->session->fresh()->status);
    }

    public function test_show_with_different_session_states(): void
    {
        Auth::login($this->user);

        // Test with started session
        $startedSession = Session::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'request_id' => $this->request->id,
            'requester_id' => $this->user->id,
            'status' => SessionStatus::STARTED,
            'scheduled_start_datetime' => now()->subHour(),
            'scheduled_end_datetime' => now()->addHour(),
        ]);

        // Mock the authorize method to return true
        $this->controller = $this->createPartialMock(SessionRequesterController::class, ['authorize']);
        $this->controller->expects($this->once())
            ->method('authorize')
            ->with('view', $startedSession)
            ->willReturn(true);

        $response = $this->controller->show($startedSession);

        $this->assertTrue($response['data']['canEnd']);
        $this->assertFalse($response['data']['canStart']);
        $this->assertFalse($response['data']['canCancel']);
    }

    public function test_show_with_expired_session(): void
    {
        Auth::login($this->user);

        // Test with expired session
        $expiredSession = Session::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'request_id' => $this->request->id,
            'requester_id' => $this->user->id,
            'status' => SessionStatus::SCHEDULED,
            'scheduled_start_datetime' => now()->subHours(2),
            'scheduled_end_datetime' => now()->subHour(), // Expired
        ]);

        // Mock the authorize method to return true
        $this->controller = $this->createPartialMock(SessionRequesterController::class, ['authorize']);
        $this->controller->expects($this->once())
            ->method('authorize')
            ->with('view', $expiredSession)
            ->willReturn(true);

        $response = $this->controller->show($expiredSession);

        $this->assertFalse($response['data']['canEnd']);
        $this->assertFalse($response['data']['canStart']);
        $this->assertFalse($response['data']['canCancel']);
    }
}
