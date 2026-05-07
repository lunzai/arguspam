<?php

namespace Tests\Unit\Notifications;

use App\Models\Asset;
use App\Models\Org;
use App\Models\Request;
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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SessionNotificationsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Session $session;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();

        $org = Org::factory()->create();
        $this->user = User::factory()->create();
        $asset = Asset::factory()->create(['org_id' => $org->id]);
        $request = Request::factory()->create([
            'org_id' => $org->id,
            'asset_id' => $asset->id,
            'requester_id' => $this->user->id,
        ]);
        $this->session = Session::factory()->create([
            'org_id' => $org->id,
            'asset_id' => $asset->id,
            'request_id' => $request->id,
            'requester_id' => $this->user->id,
            'approver_id' => $this->user->id,
        ]);
    }

    public function test_session_created_notify_requester_has_mail_channel(): void
    {
        $notification = new SessionCreatedNotifyRequester($this->session);
        $this->assertContains('mail', $notification->via($this->user));
    }

    public function test_session_created_notify_requester_has_subject_with_asset_name(): void
    {
        $notification = new SessionCreatedNotifyRequester($this->session);
        $mail = $notification->toMail($this->user);

        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertStringContainsString($this->session->asset->name, $mail->subject);
    }

    public function test_session_created_notify_approver_has_subject_with_asset_name(): void
    {
        $notification = new SessionCreatedNotifyApprover($this->session);
        $mail = $notification->toMail($this->user);

        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertStringContainsString($this->session->asset->name, $mail->subject);
    }

    public function test_session_started_notify_requester_has_subject_with_asset_name(): void
    {
        $notification = new SessionStartedNotifyRequester($this->session);
        $mail = $notification->toMail($this->user);

        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertStringContainsString($this->session->asset->name, $mail->subject);
    }

    public function test_session_started_notify_approver_has_subject_with_asset_name(): void
    {
        $notification = new SessionStartedNotifyApprover($this->session);
        $mail = $notification->toMail($this->user);

        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertStringContainsString($this->session->asset->name, $mail->subject);
    }

    public function test_session_ended_notify_requester_has_subject_with_asset_name(): void
    {
        $notification = new SessionEndedNotifyRequester($this->session);
        $mail = $notification->toMail($this->user);

        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertStringContainsString($this->session->asset->name, $mail->subject);
    }

    public function test_session_ended_notify_approver_has_subject_with_asset_name(): void
    {
        $notification = new SessionEndedNotifyApprover($this->session);
        $mail = $notification->toMail($this->user);

        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertStringContainsString($this->session->asset->name, $mail->subject);
    }

    public function test_session_cancelled_notify_requester_has_subject_with_asset_name(): void
    {
        $notification = new SessionCancelledNotifyRequester($this->session);
        $mail = $notification->toMail($this->user);

        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertStringContainsString($this->session->asset->name, $mail->subject);
    }

    public function test_session_cancelled_notify_approver_has_subject_with_asset_name(): void
    {
        $notification = new SessionCancelledNotifyApprover($this->session);
        $mail = $notification->toMail($this->user);

        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertStringContainsString($this->session->asset->name, $mail->subject);
    }

    public function test_session_terminated_notify_requester_has_subject_with_asset_name(): void
    {
        $notification = new SessionTerminatedNotifyRequester($this->session);
        $mail = $notification->toMail($this->user);

        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertStringContainsString($this->session->asset->name, $mail->subject);
    }

    public function test_session_terminated_notify_approver_has_subject_with_asset_name(): void
    {
        $notification = new SessionTerminatedNotifyApprover($this->session);
        $mail = $notification->toMail($this->user);

        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertStringContainsString($this->session->asset->name, $mail->subject);
    }

    public function test_session_expired_notify_requester_has_subject_with_asset_name(): void
    {
        $notification = new SessionExpiredNotifyRequester($this->session);
        $mail = $notification->toMail($this->user);

        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertStringContainsString($this->session->asset->name, $mail->subject);
    }

    public function test_session_expired_notify_approver_has_subject_with_asset_name(): void
    {
        $notification = new SessionExpiredNotifyApprover($this->session);
        $mail = $notification->toMail($this->user);

        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertStringContainsString($this->session->asset->name, $mail->subject);
    }
}
