<?php

namespace Tests\Unit\Notifications;

use App\Models\Asset;
use App\Models\Org;
use App\Models\Request as RequestModel;
use App\Models\Session;
use App\Models\User;
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
use App\Notifications\SessionCancelledNotifyApprover;
use App\Notifications\SessionCancelledNotifyRequester;
use App\Notifications\SessionCreatedNotifyApprover;
use App\Notifications\SessionCreatedNotifyRequester;
use App\Notifications\SessionEndedNotification;
use App\Notifications\SessionEndedNotifyApprover;
use App\Notifications\SessionEndedNotifyRequester;
use App\Notifications\SessionExpiredNotifyApprover;
use App\Notifications\SessionExpiredNotifyRequester;
use App\Notifications\SessionReviewOptionalNotifyApprover;
use App\Notifications\SessionReviewOptionalNotifyRequester;
use App\Notifications\SessionReviewRequiredNotifyApprover;
use App\Notifications\SessionReviewRequiredNotifyRequester;
use App\Notifications\SessionStartedNotification;
use App\Notifications\SessionStartedNotifyApprover;
use App\Notifications\SessionStartedNotifyRequester;
use App\Notifications\SessionTerminatedNotifyApprover;
use App\Notifications\SessionTerminatedNotifyRequester;
use Tests\TestCase;

class AllNotificationsInstantiationTest extends TestCase
{
    private User $user;
    private Org $org;
    private Asset $asset;
    private RequestModel $request;
    private Session $session;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = \Mockery::mock(User::class);
        $this->org = \Mockery::mock(Org::class);
        $this->asset = \Mockery::mock(Asset::class);
        $this->request = \Mockery::mock(RequestModel::class);
        $this->session = \Mockery::mock(Session::class);
    }

    public function test_request_approved_notify_approver_can_be_instantiated(): void
    {
        $notification = new RequestApprovedNotifyApprover($this->request);
        $this->assertInstanceOf(RequestApprovedNotifyApprover::class, $notification);
    }

    public function test_request_approved_notify_requester_can_be_instantiated(): void
    {
        $notification = new RequestApprovedNotifyRequester($this->request);
        $this->assertInstanceOf(RequestApprovedNotifyRequester::class, $notification);
    }

    public function test_request_cancelled_notify_approver_can_be_instantiated(): void
    {
        $notification = new RequestCancelledNotifyApprover($this->request);
        $this->assertInstanceOf(RequestCancelledNotifyApprover::class, $notification);
    }

    public function test_request_cancelled_notify_requester_can_be_instantiated(): void
    {
        $notification = new RequestCancelledNotifyRequester($this->request);
        $this->assertInstanceOf(RequestCancelledNotifyRequester::class, $notification);
    }

    public function test_request_expired_notification_can_be_instantiated(): void
    {
        $notification = new RequestExpiredNotification($this->request);
        $this->assertInstanceOf(RequestExpiredNotification::class, $notification);
    }

    public function test_request_expired_notify_requester_can_be_instantiated(): void
    {
        $notification = new RequestExpiredNotifyRequester($this->request);
        $this->assertInstanceOf(RequestExpiredNotifyRequester::class, $notification);
    }

    public function test_request_rejected_notify_approver_can_be_instantiated(): void
    {
        $notification = new RequestRejectedNotifyApprover($this->request);
        $this->assertInstanceOf(RequestRejectedNotifyApprover::class, $notification);
    }

    public function test_request_rejected_notify_requester_can_be_instantiated(): void
    {
        $notification = new RequestRejectedNotifyRequester($this->request);
        $this->assertInstanceOf(RequestRejectedNotifyRequester::class, $notification);
    }

    public function test_request_reminder_notify_approver_can_be_instantiated(): void
    {
        $notification = new RequestReminderNotifyApprover($this->request);
        $this->assertInstanceOf(RequestReminderNotifyApprover::class, $notification);
    }

    public function test_request_submitted_notify_approver_can_be_instantiated(): void
    {
        $notification = new RequestSubmittedNotifyApprover($this->request);
        $this->assertInstanceOf(RequestSubmittedNotifyApprover::class, $notification);
    }

    public function test_request_submitted_notify_requester_can_be_instantiated(): void
    {
        $notification = new RequestSubmittedNotifyRequester($this->request);
        $this->assertInstanceOf(RequestSubmittedNotifyRequester::class, $notification);
    }

    public function test_session_cancelled_notify_approver_can_be_instantiated(): void
    {
        $notification = new SessionCancelledNotifyApprover($this->session);
        $this->assertInstanceOf(SessionCancelledNotifyApprover::class, $notification);
    }

    public function test_session_cancelled_notify_requester_can_be_instantiated(): void
    {
        $notification = new SessionCancelledNotifyRequester($this->session);
        $this->assertInstanceOf(SessionCancelledNotifyRequester::class, $notification);
    }

    public function test_session_created_notify_approver_can_be_instantiated(): void
    {
        $notification = new SessionCreatedNotifyApprover($this->session);
        $this->assertInstanceOf(SessionCreatedNotifyApprover::class, $notification);
    }

    public function test_session_created_notify_requester_can_be_instantiated(): void
    {
        $notification = new SessionCreatedNotifyRequester($this->session);
        $this->assertInstanceOf(SessionCreatedNotifyRequester::class, $notification);
    }

    public function test_session_ended_notification_can_be_instantiated(): void
    {
        $terminationResults = ['status' => 'completed'];
        $notification = new SessionEndedNotification($this->session, $terminationResults);
        $this->assertInstanceOf(SessionEndedNotification::class, $notification);
    }

    public function test_session_ended_notify_approver_can_be_instantiated(): void
    {
        $notification = new SessionEndedNotifyApprover($this->session);
        $this->assertInstanceOf(SessionEndedNotifyApprover::class, $notification);
    }

    public function test_session_ended_notify_requester_can_be_instantiated(): void
    {
        $notification = new SessionEndedNotifyRequester($this->session);
        $this->assertInstanceOf(SessionEndedNotifyRequester::class, $notification);
    }

    public function test_session_expired_notify_approver_can_be_instantiated(): void
    {
        $notification = new SessionExpiredNotifyApprover($this->session);
        $this->assertInstanceOf(SessionExpiredNotifyApprover::class, $notification);
    }

    public function test_session_expired_notify_requester_can_be_instantiated(): void
    {
        $notification = new SessionExpiredNotifyRequester($this->session);
        $this->assertInstanceOf(SessionExpiredNotifyRequester::class, $notification);
    }

    public function test_session_review_optional_notify_approver_can_be_instantiated(): void
    {
        $notification = new SessionReviewOptionalNotifyApprover($this->session);
        $this->assertInstanceOf(SessionReviewOptionalNotifyApprover::class, $notification);
    }

    public function test_session_review_optional_notify_requester_can_be_instantiated(): void
    {
        $notification = new SessionReviewOptionalNotifyRequester($this->session);
        $this->assertInstanceOf(SessionReviewOptionalNotifyRequester::class, $notification);
    }

    public function test_session_review_required_notify_approver_can_be_instantiated(): void
    {
        $notification = new SessionReviewRequiredNotifyApprover($this->session);
        $this->assertInstanceOf(SessionReviewRequiredNotifyApprover::class, $notification);
    }

    public function test_session_review_required_notify_requester_can_be_instantiated(): void
    {
        $notification = new SessionReviewRequiredNotifyRequester($this->session);
        $this->assertInstanceOf(SessionReviewRequiredNotifyRequester::class, $notification);
    }

    public function test_session_started_notification_can_be_instantiated(): void
    {
        $notification = new SessionStartedNotification($this->session);
        $this->assertInstanceOf(SessionStartedNotification::class, $notification);
    }

    public function test_session_started_notify_approver_can_be_instantiated(): void
    {
        $notification = new SessionStartedNotifyApprover($this->session);
        $this->assertInstanceOf(SessionStartedNotifyApprover::class, $notification);
    }

    public function test_session_started_notify_requester_can_be_instantiated(): void
    {
        $notification = new SessionStartedNotifyRequester($this->session);
        $this->assertInstanceOf(SessionStartedNotifyRequester::class, $notification);
    }

    public function test_session_terminated_notify_approver_can_be_instantiated(): void
    {
        $notification = new SessionTerminatedNotifyApprover($this->session);
        $this->assertInstanceOf(SessionTerminatedNotifyApprover::class, $notification);
    }

    public function test_session_terminated_notify_requester_can_be_instantiated(): void
    {
        $notification = new SessionTerminatedNotifyRequester($this->session);
        $this->assertInstanceOf(SessionTerminatedNotifyRequester::class, $notification);
    }

    public function test_all_notifications_implement_notification_interface(): void
    {
        $notifications = [
            new RequestApprovedNotifyApprover($this->request),
            new RequestApprovedNotifyRequester($this->request),
            new RequestCancelledNotifyApprover($this->request),
            new RequestCancelledNotifyRequester($this->request),
            new RequestExpiredNotification($this->request),
            new RequestExpiredNotifyRequester($this->request),
            new RequestRejectedNotifyApprover($this->request),
            new RequestRejectedNotifyRequester($this->request),
            new RequestReminderNotifyApprover($this->request),
            new RequestSubmittedNotifyApprover($this->request),
            new RequestSubmittedNotifyRequester($this->request),
            new SessionCancelledNotifyApprover($this->session),
            new SessionCancelledNotifyRequester($this->session),
            new SessionCreatedNotifyApprover($this->session),
            new SessionCreatedNotifyRequester($this->session),
            new SessionEndedNotification($this->session, ['status' => 'completed']),
            new SessionEndedNotifyApprover($this->session),
            new SessionEndedNotifyRequester($this->session),
            new SessionExpiredNotifyApprover($this->session),
            new SessionExpiredNotifyRequester($this->session),
            new SessionReviewOptionalNotifyApprover($this->session),
            new SessionReviewOptionalNotifyRequester($this->session),
            new SessionReviewRequiredNotifyApprover($this->session),
            new SessionReviewRequiredNotifyRequester($this->session),
            new SessionStartedNotification($this->session),
            new SessionStartedNotifyApprover($this->session),
            new SessionStartedNotifyRequester($this->session),
            new SessionTerminatedNotifyApprover($this->session),
            new SessionTerminatedNotifyRequester($this->session),
        ];

        foreach ($notifications as $notification) {
            $this->assertInstanceOf(\Illuminate\Notifications\Notification::class, $notification);
        }
    }

    public function test_all_notifications_have_via_method(): void
    {
        $notifications = [
            new RequestApprovedNotifyApprover($this->request),
            new RequestApprovedNotifyRequester($this->request),
            new RequestCancelledNotifyApprover($this->request),
            new RequestCancelledNotifyRequester($this->request),
            new RequestExpiredNotification($this->request),
            new RequestExpiredNotifyRequester($this->request),
            new RequestRejectedNotifyApprover($this->request),
            new RequestRejectedNotifyRequester($this->request),
            new RequestReminderNotifyApprover($this->request),
            new RequestSubmittedNotifyApprover($this->request),
            new RequestSubmittedNotifyRequester($this->request),
            new SessionCancelledNotifyApprover($this->session),
            new SessionCancelledNotifyRequester($this->session),
            new SessionCreatedNotifyApprover($this->session),
            new SessionCreatedNotifyRequester($this->session),
            new SessionEndedNotification($this->session, ['status' => 'completed']),
            new SessionEndedNotifyApprover($this->session),
            new SessionEndedNotifyRequester($this->session),
            new SessionExpiredNotifyApprover($this->session),
            new SessionExpiredNotifyRequester($this->session),
            new SessionReviewOptionalNotifyApprover($this->session),
            new SessionReviewOptionalNotifyRequester($this->session),
            new SessionReviewRequiredNotifyApprover($this->session),
            new SessionReviewRequiredNotifyRequester($this->session),
            new SessionStartedNotification($this->session),
            new SessionStartedNotifyApprover($this->session),
            new SessionStartedNotifyRequester($this->session),
            new SessionTerminatedNotifyApprover($this->session),
            new SessionTerminatedNotifyRequester($this->session),
        ];

        foreach ($notifications as $notification) {
            $this->assertTrue(method_exists($notification, 'via'));
        }
    }

    public function test_all_notifications_have_to_mail_method(): void
    {
        $notifications = [
            new RequestApprovedNotifyApprover($this->request),
            new RequestApprovedNotifyRequester($this->request),
            new RequestCancelledNotifyApprover($this->request),
            new RequestCancelledNotifyRequester($this->request),
            new RequestExpiredNotification($this->request),
            new RequestExpiredNotifyRequester($this->request),
            new RequestRejectedNotifyApprover($this->request),
            new RequestRejectedNotifyRequester($this->request),
            new RequestReminderNotifyApprover($this->request),
            new RequestSubmittedNotifyApprover($this->request),
            new RequestSubmittedNotifyRequester($this->request),
            new SessionCancelledNotifyApprover($this->session),
            new SessionCancelledNotifyRequester($this->session),
            new SessionCreatedNotifyApprover($this->session),
            new SessionCreatedNotifyRequester($this->session),
            new SessionEndedNotification($this->session, ['status' => 'completed']),
            new SessionEndedNotifyApprover($this->session),
            new SessionEndedNotifyRequester($this->session),
            new SessionExpiredNotifyApprover($this->session),
            new SessionExpiredNotifyRequester($this->session),
            new SessionReviewOptionalNotifyApprover($this->session),
            new SessionReviewOptionalNotifyRequester($this->session),
            new SessionReviewRequiredNotifyApprover($this->session),
            new SessionReviewRequiredNotifyRequester($this->session),
            new SessionStartedNotification($this->session),
            new SessionStartedNotifyApprover($this->session),
            new SessionStartedNotifyRequester($this->session),
            new SessionTerminatedNotifyApprover($this->session),
            new SessionTerminatedNotifyRequester($this->session),
        ];

        foreach ($notifications as $notification) {
            $this->assertTrue(method_exists($notification, 'toMail'));
        }
    }

    public function test_all_notifications_have_to_array_method(): void
    {
        $notifications = [
            new RequestApprovedNotifyApprover($this->request),
            new RequestApprovedNotifyRequester($this->request),
            new RequestCancelledNotifyApprover($this->request),
            new RequestCancelledNotifyRequester($this->request),
            new RequestExpiredNotification($this->request),
            new RequestExpiredNotifyRequester($this->request),
            new RequestRejectedNotifyApprover($this->request),
            new RequestRejectedNotifyRequester($this->request),
            new RequestReminderNotifyApprover($this->request),
            new RequestSubmittedNotifyApprover($this->request),
            new RequestSubmittedNotifyRequester($this->request),
            new SessionCancelledNotifyApprover($this->session),
            new SessionCancelledNotifyRequester($this->session),
            new SessionCreatedNotifyApprover($this->session),
            new SessionCreatedNotifyRequester($this->session),
            new SessionEndedNotification($this->session, ['status' => 'completed']),
            new SessionEndedNotifyApprover($this->session),
            new SessionEndedNotifyRequester($this->session),
            new SessionExpiredNotifyApprover($this->session),
            new SessionExpiredNotifyRequester($this->session),
            new SessionReviewOptionalNotifyApprover($this->session),
            new SessionReviewOptionalNotifyRequester($this->session),
            new SessionReviewRequiredNotifyApprover($this->session),
            new SessionReviewRequiredNotifyRequester($this->session),
            new SessionStartedNotification($this->session),
            new SessionStartedNotifyApprover($this->session),
            new SessionStartedNotifyRequester($this->session),
            new SessionTerminatedNotifyApprover($this->session),
            new SessionTerminatedNotifyRequester($this->session),
        ];

        foreach ($notifications as $notification) {
            $this->assertTrue(method_exists($notification, 'toArray'));
        }
    }
}
