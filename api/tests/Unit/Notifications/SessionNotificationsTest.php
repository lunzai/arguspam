<?php

namespace Tests\Unit\Notifications;

use App\Models\Asset;
use App\Models\Session;
use App\Notifications\SessionEndedNotification;
use App\Notifications\SessionStartedNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Tests\TestCase;

class SessionNotificationsTest extends TestCase
{
    public function test_session_started_via_mail_and_database(): void
    {
        $notifiable = $this->fakeNotifiable();
        $session = $this->fakeSession($notifiable);
        $notif = new SessionStartedNotification($session);

        $via = $notif->via($notifiable);
        $this->assertSame(['mail', 'database'], $via);

        $mail = $notif->toMail($notifiable);
        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertStringContainsString('Session Started', $this->extractSubject($mail));

        $array = $notif->toArray($notifiable);
        $this->assertSame('session_started', $array['type']);
        $this->assertSame($session->id, $array['session_id']);
    }

    public function test_session_ended_via_mail_and_database(): void
    {
        $notifiable = $this->fakeNotifiable();
        $session = $this->fakeSession($notifiable);
        $terminationResults = ['audit_log_count' => 5];
        $notif = new SessionEndedNotification($session, $terminationResults);

        $via = $notif->via($notifiable);
        $this->assertSame(['mail', 'database'], $via);

        $mail = $notif->toMail($notifiable);
        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertStringContainsString('Session Ended', $this->extractSubject($mail));

        $array = $notif->toArray($notifiable);
        $this->assertSame('session_ended', $array['type']);
        $this->assertSame($session->id, $array['session_id']);
        $this->assertSame(5, $array['audit_logs_count']);
    }

    private function fakeSession(object $notifiable): Session
    {
        $asset = new Asset;
        $asset->name = 'DB Prod';

        // Lightweight concrete subclass to provide getRemainingDuration()
        $session = new class extends Session
        {
            public function getRemainingDuration(): int
            {
                return 1800;
            }
        };
        $session->id = 321;
        $session->setRelation('asset', $asset);
        $session->setRelation('requester', $notifiable);
        $session->checked_in_at = now();
        $session->scheduled_end_datetime = now()->addHour();
        $session->actual_duration = 42;
        $session->is_terminated = false;

        return $session;
    }

    private function fakeNotifiable(): object
    {
        return new class
        {
            public int $id = 10;
            public string $name = 'John Doe';
            public function is($other): bool
            {
                return $other === $this || (isset($other->id) && $other->id === $this->id);
            }
        };
    }

    private function extractSubject(MailMessage $mail): string
    {
        return (string) ($mail->subject ?? '');
    }
}
