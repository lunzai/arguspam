<?php

namespace Tests\Unit\Listeners;

use App\Events\SessionCancelled;
use App\Events\SessionCreated;
use App\Events\SessionEnded;
use App\Events\SessionExpired;
use App\Events\SessionStarted;
use App\Events\SessionTerminated;
use App\Listeners\HandleSessionCancelled;
use App\Listeners\HandleSessionCreated;
use App\Listeners\HandleSessionEnded;
use App\Listeners\HandleSessionEndedOrTerminated;
use App\Listeners\HandleSessionExpired;
use App\Listeners\HandleSessionStarted;
use App\Listeners\HandleSessionTerminated;
use App\Models\AssetAccount;
use App\Models\Session;
use App\Models\User;
use App\Notifications\SessionCancelledNotifyApprover;
use App\Notifications\SessionCancelledNotifyRequester;
use App\Notifications\SessionCreatedNotifyApprover;
use App\Notifications\SessionCreatedNotifyRequester;
use App\Notifications\SessionEndedNotifyApprover;
use App\Notifications\SessionEndedNotifyRequester;
use App\Notifications\SessionExpiredNotifyApprover;
use App\Notifications\SessionExpiredNotifyRequester;
use App\Notifications\SessionStartedNotifyApprover;
use App\Notifications\SessionStartedNotifyRequester;
use App\Notifications\SessionTerminatedNotifyApprover;
use App\Notifications\SessionTerminatedNotifyRequester;
use App\Services\Jit\JitManager;
use App\Services\Jit\Secrets\SecretsManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SessionListenerTest extends TestCase
{
    use RefreshDatabase;

    private User $requester;
    private User $approver;
    private Session $session;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        Notification::fake();

        $this->requester = User::factory()->create();
        $this->approver = User::factory()->create();

        $this->session = Session::factory()->create([
            'requester_id' => $this->requester->id,
            'approver_id' => $this->approver->id,
        ]);
    }

    public function test_handle_session_created_notifies_requester(): void
    {
        $listener = new HandleSessionCreated;
        $listener->handle(new SessionCreated($this->session));

        Notification::assertSentTo($this->requester, SessionCreatedNotifyRequester::class);
    }

    public function test_handle_session_created_notifies_approver_when_different(): void
    {
        $listener = new HandleSessionCreated;
        $listener->handle(new SessionCreated($this->session));

        Notification::assertSentTo($this->approver, SessionCreatedNotifyApprover::class);
    }

    public function test_handle_session_created_does_not_double_notify_when_same_user(): void
    {
        $this->session->update(['approver_id' => $this->requester->id]);
        $this->session->refresh();

        $listener = new HandleSessionCreated;
        $listener->handle(new SessionCreated($this->session));

        Notification::assertSentTo($this->requester, SessionCreatedNotifyRequester::class);
        Notification::assertNotSentTo($this->requester, SessionCreatedNotifyApprover::class);
    }

    public function test_handle_session_started_notifies_requester(): void
    {
        $listener = new HandleSessionStarted;
        $listener->handle(new SessionStarted($this->session));

        Notification::assertSentTo($this->requester, SessionStartedNotifyRequester::class);
    }

    public function test_handle_session_started_notifies_approver(): void
    {
        $listener = new HandleSessionStarted;
        $listener->handle(new SessionStarted($this->session));

        Notification::assertSentTo($this->approver, SessionStartedNotifyApprover::class);
    }

    public function test_handle_session_ended_notifies_requester(): void
    {
        $listener = new HandleSessionEnded;
        $listener->handle(new SessionEnded($this->session, []));

        Notification::assertSentTo($this->requester, SessionEndedNotifyRequester::class);
    }

    public function test_handle_session_ended_notifies_approver(): void
    {
        $listener = new HandleSessionEnded;
        $listener->handle(new SessionEnded($this->session, []));

        Notification::assertSentTo($this->approver, SessionEndedNotifyApprover::class);
    }

    public function test_handle_session_cancelled_notifies_requester(): void
    {
        $listener = new HandleSessionCancelled;
        $listener->handle(new SessionCancelled($this->session));

        Notification::assertSentTo($this->requester, SessionCancelledNotifyRequester::class);
    }

    public function test_handle_session_cancelled_notifies_approver(): void
    {
        $listener = new HandleSessionCancelled;
        $listener->handle(new SessionCancelled($this->session));

        Notification::assertSentTo($this->approver, SessionCancelledNotifyApprover::class);
    }

    public function test_handle_session_terminated_notifies_requester(): void
    {
        $listener = new HandleSessionTerminated;
        $listener->handle(new SessionTerminated($this->session));

        Notification::assertSentTo($this->requester, SessionTerminatedNotifyRequester::class);
    }

    public function test_handle_session_terminated_notifies_approver(): void
    {
        $listener = new HandleSessionTerminated;
        $listener->handle(new SessionTerminated($this->session));

        Notification::assertSentTo($this->approver, SessionTerminatedNotifyApprover::class);
    }

    public function test_handle_session_expired_notifies_requester(): void
    {
        $listener = new HandleSessionExpired;
        $listener->handle(new SessionExpired($this->session));

        Notification::assertSentTo($this->requester, SessionExpiredNotifyRequester::class);
    }

    public function test_handle_session_expired_notifies_approver(): void
    {
        $listener = new HandleSessionExpired;
        $listener->handle(new SessionExpired($this->session));

        Notification::assertSentTo($this->approver, SessionExpiredNotifyApprover::class);
    }

    public function test_handle_session_ended_or_terminated_calls_terminate_jit_account(): void
    {
        $assetAccount = AssetAccount::factory()->create([
            'asset_id' => $this->session->asset_id,
        ]);
        $this->session->update(['asset_account_id' => $assetAccount->id]);

        $this->mock(JitManager::class, function ($mock) {
            $mock->shouldReceive('terminateAccount')->once()->andReturn(true);
        });

        $secretsManager = $this->mock(SecretsManager::class);

        $listener = new HandleSessionEndedOrTerminated($secretsManager);
        $listener->handle(new SessionEnded($this->session->fresh(), []));

        $this->session->refresh();
        $this->assertNotNull($this->session->account_revoked_at);
    }
}
