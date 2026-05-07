<?php

namespace Tests\Unit\Listeners;

use App\Enums\AssetAccessRole;
use App\Events\RequestApproved;
use App\Events\RequestCancelled;
use App\Events\RequestExpired;
use App\Events\RequestRejected;
use App\Events\RequestSubmitted;
use App\Listeners\HandleRequestApproved;
use App\Listeners\HandleRequestCancelled;
use App\Listeners\HandleRequestExpired;
use App\Listeners\HandleRequestRejected;
use App\Listeners\HandleRequestSubmitted;
use App\Models\Asset;
use App\Models\AssetAccessGrant;
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
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RequestListenerTest extends TestCase
{
    use RefreshDatabase;

    private User $requester;
    private User $approver;
    private Asset $asset;
    private Request $request;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        Notification::fake();

        $org = Org::factory()->create();
        $this->requester = User::factory()->create();
        $this->approver = User::factory()->create();
        $org->users()->attach([$this->requester->id, $this->approver->id]);

        $this->asset = Asset::factory()->create(['org_id' => $org->id]);
        AssetAccessGrant::factory()->create([
            'asset_id' => $this->asset->id,
            'user_id' => $this->approver->id,
            'user_group_id' => null,
            'role' => AssetAccessRole::APPROVER,
        ]);

        $this->request = Request::factory()->create([
            'org_id' => $org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->requester->id,
        ]);
    }

    public function test_handle_request_submitted_notifies_requester(): void
    {
        $listener = new HandleRequestSubmitted;
        $listener->handle(new RequestSubmitted($this->request));

        Notification::assertSentTo($this->requester, RequestSubmittedNotifyRequester::class);
    }

    public function test_handle_request_submitted_notifies_approvers(): void
    {
        $listener = new HandleRequestSubmitted;
        $listener->handle(new RequestSubmitted($this->request));

        Notification::assertSentTo($this->approver, RequestSubmittedNotifyApprover::class);
    }

    public function test_handle_request_rejected_notifies_requester(): void
    {
        $listener = new HandleRequestRejected;
        $listener->handle(new RequestRejected($this->request));

        Notification::assertSentTo($this->requester, RequestRejectedNotifyRequester::class);
    }

    public function test_handle_request_rejected_notifies_approvers(): void
    {
        $listener = new HandleRequestRejected;
        $listener->handle(new RequestRejected($this->request));

        Notification::assertSentTo($this->approver, RequestRejectedNotifyApprover::class);
    }

    public function test_handle_request_cancelled_notifies_requester(): void
    {
        $listener = new HandleRequestCancelled;
        $listener->handle(new RequestCancelled($this->request));

        Notification::assertSentTo($this->requester, RequestCancelledNotifyRequester::class);
    }

    public function test_handle_request_cancelled_notifies_approvers(): void
    {
        $listener = new HandleRequestCancelled;
        $listener->handle(new RequestCancelled($this->request));

        Notification::assertSentTo($this->approver, RequestCancelledNotifyApprover::class);
    }

    public function test_handle_request_expired_notifies_requester(): void
    {
        $listener = new HandleRequestExpired;
        $listener->handle(new RequestExpired($this->request));

        Notification::assertSentTo($this->requester, RequestExpiredNotification::class);
    }

    public function test_handle_request_approved_notifies_requester(): void
    {
        $this->request->update(['approved_by' => $this->approver->id]);

        $listener = new HandleRequestApproved;
        $listener->handle(new RequestApproved($this->request->fresh()));

        Notification::assertSentTo($this->requester, RequestApprovedNotifyRequester::class);
    }

    public function test_handle_request_approved_notifies_approvers(): void
    {
        $this->request->update(['approved_by' => $this->approver->id]);

        $listener = new HandleRequestApproved;
        $listener->handle(new RequestApproved($this->request->fresh()));

        Notification::assertSentTo($this->approver, RequestApprovedNotifyApprover::class);
    }

    public function test_handle_request_approved_creates_session(): void
    {
        $this->request->update(['approved_by' => $this->approver->id]);

        $listener = new HandleRequestApproved;
        $listener->handle(new RequestApproved($this->request->fresh()));

        $this->assertDatabaseHas('sessions', [
            'request_id' => $this->request->id,
        ]);
    }
}
