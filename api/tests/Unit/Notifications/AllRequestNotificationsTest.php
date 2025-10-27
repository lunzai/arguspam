<?php

namespace Tests\Unit\Notifications;

use App\Models\Asset;
use App\Models\Request;
use App\Notifications\RequestApprovedNotifyApprover;
use App\Notifications\RequestApprovedNotifyRequester;
use App\Notifications\RequestCancelledNotifyApprover;
use App\Notifications\RequestCancelledNotifyRequester;
use App\Notifications\RequestExpiredNotification;
use App\Notifications\RequestExpiredNotifyRequester;
use App\Notifications\RequestRejectedNotifyApprover;
use App\Notifications\RequestRejectedNotifyRequester;
use App\Notifications\RequestReminderNotifyApprover;
use App\Notifications\RequestSubmittedNotifyApprover;
use App\Notifications\RequestSubmittedNotifyRequester;
use Illuminate\Notifications\Messages\MailMessage;
use Tests\TestCase;

class AllRequestNotificationsTest extends TestCase
{
    public function test_request_submitted_notify_approver(): void
    {
        $request = $this->fakeRequest();
        $notif = new RequestSubmittedNotifyApprover($request);

        $via = $notif->via($this->fakeNotifiable());
        $this->assertSame(['mail'], $via);

        $mail = $notif->toMail($this->fakeNotifiable());
        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertStringContainsString('Request Awaiting Approval', $this->extractSubject($mail));
        $this->assertStringContainsString($request->asset->name, $this->extractSubject($mail));
        $this->assertStringContainsString('/requests/'.$request->id, $this->extractMarkdownViewDataUrl($mail));

        // Test toArray method for 100% coverage
        $array = $notif->toArray($this->fakeNotifiable());
        $this->assertIsArray($array);
        $this->assertEmpty($array);
    }

    public function test_request_submitted_notify_requester(): void
    {
        $request = $this->fakeRequest();
        $notif = new RequestSubmittedNotifyRequester($request);

        $via = $notif->via($this->fakeNotifiable());
        $this->assertSame(['mail'], $via);

        $mail = $notif->toMail($this->fakeNotifiable());
        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertStringContainsString('Request Submitted', $this->extractSubject($mail));
        $this->assertStringContainsString($request->asset->name, $this->extractSubject($mail));
        $this->assertStringContainsString('/requests/'.$request->id, $this->extractMarkdownViewDataUrl($mail));

        // Test toArray method for 100% coverage
        $array = $notif->toArray($this->fakeNotifiable());
        $this->assertIsArray($array);
        $this->assertEmpty($array);
    }

    public function test_request_approved_notify_approver(): void
    {
        $request = $this->fakeRequest();
        $notif = new RequestApprovedNotifyApprover($request);

        $via = $notif->via($this->fakeNotifiable());
        $this->assertSame(['mail'], $via);

        $mail = $notif->toMail($this->fakeNotifiable());
        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertStringContainsString('Request Approved', $this->extractSubject($mail));
        $this->assertStringContainsString($request->asset->name, $this->extractSubject($mail));
        $this->assertStringContainsString('/requests/'.$request->id, $this->extractMarkdownViewDataUrl($mail));

        // Test toArray method for 100% coverage
        $array = $notif->toArray($this->fakeNotifiable());
        $this->assertIsArray($array);
        $this->assertEmpty($array);
    }

    public function test_request_approved_notify_requester(): void
    {
        $request = $this->fakeRequest();
        $notif = new RequestApprovedNotifyRequester($request);

        $via = $notif->via($this->fakeNotifiable());
        $this->assertSame(['mail'], $via);

        $mail = $notif->toMail($this->fakeNotifiable());
        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertStringContainsString('Request Approved', $this->extractSubject($mail));
        $this->assertStringContainsString($request->asset->name, $this->extractSubject($mail));
        $this->assertStringContainsString('/requests/'.$request->id, $this->extractMarkdownViewDataUrl($mail));

        // Test toArray method for 100% coverage
        $array = $notif->toArray($this->fakeNotifiable());
        $this->assertIsArray($array);
        $this->assertEmpty($array);
    }

    public function test_request_cancelled_notify_approver(): void
    {
        $request = $this->fakeRequest();
        $notif = new RequestCancelledNotifyApprover($request);

        $via = $notif->via($this->fakeNotifiable());
        $this->assertSame(['mail'], $via);

        $mail = $notif->toMail($this->fakeNotifiable());
        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertStringContainsString('Request Cancelled', $this->extractSubject($mail));
        $this->assertStringContainsString($request->asset->name, $this->extractSubject($mail));
        $this->assertStringContainsString('/requests/'.$request->id, $this->extractMarkdownViewDataUrl($mail));

        // Test toArray method for 100% coverage
        $array = $notif->toArray($this->fakeNotifiable());
        $this->assertIsArray($array);
        $this->assertEmpty($array);
    }

    public function test_request_cancelled_notify_requester(): void
    {
        $request = $this->fakeRequest();
        $notif = new RequestCancelledNotifyRequester($request);

        $via = $notif->via($this->fakeNotifiable());
        $this->assertSame(['mail'], $via);

        $mail = $notif->toMail($this->fakeNotifiable());
        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertStringContainsString('Request Cancelled', $this->extractSubject($mail));
        $this->assertStringContainsString($request->asset->name, $this->extractSubject($mail));
        $this->assertStringContainsString('/requests/'.$request->id, $this->extractMarkdownViewDataUrl($mail));

        // Test toArray method for 100% coverage
        $array = $notif->toArray($this->fakeNotifiable());
        $this->assertIsArray($array);
        $this->assertEmpty($array);
    }

    public function test_request_rejected_notify_approver(): void
    {
        $request = $this->fakeRequest();
        $notif = new RequestRejectedNotifyApprover($request);

        $via = $notif->via($this->fakeNotifiable());
        $this->assertSame(['mail'], $via);

        $mail = $notif->toMail($this->fakeNotifiable());
        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertStringContainsString('Request Rejected', $this->extractSubject($mail));
        $this->assertStringContainsString($request->asset->name, $this->extractSubject($mail));
        $this->assertStringContainsString('/requests/'.$request->id, $this->extractMarkdownViewDataUrl($mail));

        // Test toArray method for 100% coverage
        $array = $notif->toArray($this->fakeNotifiable());
        $this->assertIsArray($array);
        $this->assertEmpty($array);
    }

    public function test_request_rejected_notify_requester(): void
    {
        $request = $this->fakeRequest();
        $notif = new RequestRejectedNotifyRequester($request);

        $via = $notif->via($this->fakeNotifiable());
        $this->assertSame(['mail'], $via);

        $mail = $notif->toMail($this->fakeNotifiable());
        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertStringContainsString('Request Rejected', $this->extractSubject($mail));
        $this->assertStringContainsString($request->asset->name, $this->extractSubject($mail));
        $this->assertStringContainsString('/requests/'.$request->id, $this->extractMarkdownViewDataUrl($mail));

        // Test toArray method for 100% coverage
        $array = $notif->toArray($this->fakeNotifiable());
        $this->assertIsArray($array);
        $this->assertEmpty($array);
    }

    public function test_request_expired_notification(): void
    {
        $request = $this->fakeRequest();
        $notif = new RequestExpiredNotification($request);

        $via = $notif->via($this->fakeNotifiable());
        $this->assertSame(['mail'], $via);

        $mail = $notif->toMail($this->fakeNotifiable());
        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertStringContainsString('Request Expired', $this->extractSubject($mail));
        $this->assertStringContainsString($request->asset->name, $this->extractSubject($mail));
        $this->assertStringContainsString('/requests/'.$request->id, $this->extractMarkdownViewDataUrl($mail));

        // Test toArray method for 100% coverage
        $array = $notif->toArray($this->fakeNotifiable());
        $this->assertIsArray($array);
        $this->assertEmpty($array);
    }

    public function test_request_expired_notify_requester(): void
    {
        $request = $this->fakeRequest();
        $notif = new RequestExpiredNotifyRequester($request);

        $via = $notif->via($this->fakeNotifiable());
        $this->assertSame(['mail'], $via);

        $mail = $notif->toMail($this->fakeNotifiable());
        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertStringContainsString('Request Expired', $this->extractSubject($mail));
        $this->assertStringContainsString($request->asset->name, $this->extractSubject($mail));
        $this->assertStringContainsString('/requests/'.$request->id, $this->extractMarkdownViewDataUrl($mail));

        // Test toArray method for 100% coverage
        $array = $notif->toArray($this->fakeNotifiable());
        $this->assertIsArray($array);
        $this->assertEmpty($array);
    }

    public function test_request_reminder_notify_approver(): void
    {
        $request = $this->fakeRequest();
        $notif = new RequestReminderNotifyApprover($request);

        $via = $notif->via($this->fakeNotifiable());
        $this->assertSame(['mail'], $via);

        $mail = $notif->toMail($this->fakeNotifiable());
        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertStringContainsString('URGENT: Request Expiring Soon', $this->extractSubject($mail));
        $this->assertStringContainsString($request->asset->name, $this->extractSubject($mail));
        $this->assertStringContainsString('/requests/'.$request->id, $this->extractMarkdownViewDataUrl($mail));

        // Test toArray method for 100% coverage
        $array = $notif->toArray($this->fakeNotifiable());
        $this->assertIsArray($array);
        $this->assertEmpty($array);
    }

    private function fakeRequest(): Request
    {
        $asset = new Asset;
        $asset->name = 'Test Asset';

        $request = new Request;
        $request->id = 123;
        $request->org_id = 1;
        $request->asset_id = 2;
        $request->setRelation('asset', $asset);

        return $request;
    }

    private function fakeNotifiable(): object
    {
        return new class
        {
            public int $id = 10;
            public string $name = 'John Doe';
        };
    }

    private function extractSubject(MailMessage $mail): string
    {
        return (string) ($mail->subject ?? '');
    }

    private function extractMarkdownViewDataUrl(MailMessage $mail): string
    {
        $viewData = $mail->viewData ?? [];
        return (string) ($viewData['url'] ?? '');
    }
}
