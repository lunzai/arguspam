<?php

namespace Tests\Unit\Notifications;

use App\Models\Asset;
use App\Models\Org;
use App\Models\Request;
use App\Models\User;
use App\Notifications\RequestApprovedNotifyApprover;
use App\Notifications\RequestApprovedNotifyRequester;
use App\Notifications\RequestCancelledNotifyApprover;
use App\Notifications\RequestCancelledNotifyRequester;
use App\Notifications\RequestExpiredNotification;
use App\Notifications\RequestRejectedNotifyApprover;
use App\Notifications\RequestRejectedNotifyRequester;
use App\Notifications\RequestSubmittedNotifyApprover;
use App\Notifications\RequestSubmittedNotifyRequester;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RequestNotificationsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Request $request;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();

        $org = Org::factory()->create();
        $this->user = User::factory()->create();
        $asset = Asset::factory()->create(['org_id' => $org->id]);
        $this->request = Request::factory()->create([
            'org_id' => $org->id,
            'asset_id' => $asset->id,
            'requester_id' => $this->user->id,
        ]);
    }

    public function test_request_approved_notify_requester_has_mail_channel(): void
    {
        $notification = new RequestApprovedNotifyRequester($this->request);
        $this->assertContains('mail', $notification->via($this->user));
    }

    public function test_request_approved_notify_requester_has_subject_with_asset_name(): void
    {
        $notification = new RequestApprovedNotifyRequester($this->request);
        $mail = $notification->toMail($this->user);

        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertStringContainsString($this->request->asset->name, $mail->subject);
    }

    public function test_request_approved_notify_approver_has_mail_channel(): void
    {
        $notification = new RequestApprovedNotifyApprover($this->request);
        $this->assertContains('mail', $notification->via($this->user));
    }

    public function test_request_approved_notify_approver_has_subject_with_asset_name(): void
    {
        $notification = new RequestApprovedNotifyApprover($this->request);
        $mail = $notification->toMail($this->user);

        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertStringContainsString($this->request->asset->name, $mail->subject);
    }

    public function test_request_rejected_notify_requester_has_subject_with_asset_name(): void
    {
        $notification = new RequestRejectedNotifyRequester($this->request);
        $mail = $notification->toMail($this->user);

        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertStringContainsString($this->request->asset->name, $mail->subject);
    }

    public function test_request_rejected_notify_approver_has_subject_with_asset_name(): void
    {
        $notification = new RequestRejectedNotifyApprover($this->request);
        $mail = $notification->toMail($this->user);

        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertStringContainsString($this->request->asset->name, $mail->subject);
    }

    public function test_request_cancelled_notify_requester_has_subject_with_asset_name(): void
    {
        $notification = new RequestCancelledNotifyRequester($this->request);
        $mail = $notification->toMail($this->user);

        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertStringContainsString($this->request->asset->name, $mail->subject);
    }

    public function test_request_cancelled_notify_approver_has_subject_with_asset_name(): void
    {
        $notification = new RequestCancelledNotifyApprover($this->request);
        $mail = $notification->toMail($this->user);

        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertStringContainsString($this->request->asset->name, $mail->subject);
    }

    public function test_request_submitted_notify_requester_has_subject_with_asset_name(): void
    {
        $notification = new RequestSubmittedNotifyRequester($this->request);
        $mail = $notification->toMail($this->user);

        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertStringContainsString($this->request->asset->name, $mail->subject);
    }

    public function test_request_submitted_notify_approver_has_subject_with_asset_name(): void
    {
        $notification = new RequestSubmittedNotifyApprover($this->request);
        $mail = $notification->toMail($this->user);

        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertStringContainsString($this->request->asset->name, $mail->subject);
    }

    public function test_request_expired_notification_has_subject_with_asset_name(): void
    {
        $notification = new RequestExpiredNotification($this->request);
        $mail = $notification->toMail($this->user);

        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertStringContainsString($this->request->asset->name, $mail->subject);
    }
}
