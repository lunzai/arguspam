<?php

namespace Tests\Unit\Notifications;

use App\Models\Asset;
use App\Models\Request;
use App\Notifications\RequestApprovedNotifyRequester;
use App\Notifications\RequestSubmittedNotifyApprover;
use Illuminate\Notifications\Messages\MailMessage;
use Tests\TestCase;

class RequestNotificationsTest extends TestCase
{
    public function test_request_submitted_notify_approver_via_mail(): void
    {
        $request = $this->fakeRequest();
        $notif = new RequestSubmittedNotifyApprover($request);

        $via = $notif->via($this->fakeNotifiable());
        $this->assertSame(['mail'], $via);

        $mail = $notif->toMail($this->fakeNotifiable());
        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertStringContainsString('Request Awaiting Approval', $this->extractSubject($mail));
        $this->assertStringContainsString((string) $request->asset->name, $this->extractSubject($mail));
        $this->assertStringContainsString('/requests/'.$request->id, $this->extractMarkdownViewDataUrl($mail));

        // Test toArray method for 100% coverage
        $array = $notif->toArray($this->fakeNotifiable());
        $this->assertIsArray($array);
        $this->assertEmpty($array);
    }

    public function test_request_approved_notify_requester_via_mail(): void
    {
        $request = $this->fakeRequest();
        $notif = new RequestApprovedNotifyRequester($request);

        $via = $notif->via($this->fakeNotifiable());
        $this->assertSame(['mail'], $via);

        $mail = $notif->toMail($this->fakeNotifiable());
        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertStringContainsString('Request Approved', $this->extractSubject($mail));
        $this->assertStringContainsString((string) $request->asset->name, $this->extractSubject($mail));
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
        // Minimal notifiable with name used by some notifications
        return (object) ['name' => 'Jane Doe'];
    }

    private function extractSubject(MailMessage $mail): string
    {
        // MailMessage stores subject on public property
        return (string) ($mail->subject ?? '');
    }

    private function extractMarkdownViewDataUrl(MailMessage $mail): string
    {
        // When using markdown(), MailMessage keeps viewData with provided array
        $viewData = $mail->viewData ?? [];
        return (string) ($viewData['url'] ?? '');
    }
}
