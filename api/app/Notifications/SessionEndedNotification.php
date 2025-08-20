<?php

namespace App\Notifications;

use App\Models\Session;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SessionEndedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    protected $terminationResults;

    protected $session;

    /**
     * Create a new notification instance.
     */
    public function __construct(Session $session, array $terminationResults)
    {
        $this->session = $session;
        $this->terminationResults = $terminationResults;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Database Session Ended')
            ->greeting('Hello '.$notifiable->name)
            ->line('Your database session has been ended.')
            ->line('Asset: '.$this->session->asset->name)
            ->line('Duration: '.$this->session->actual_duration.' minutes');

        if ($this->session->is_terminated) {
            $mail->line('This session was terminated by an administrator.');
        }

        if ($this->terminationResults['audit_log_count'] ?? 0 > 0) {
            $mail->line('Query logs: '.$this->terminationResults['audit_log_count'].' queries recorded');
        }

        return $mail->action('View Session Report', url('/sessions/'.$this->session->id.'/report'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'session_ended',
            'session_id' => $this->session->id,
            'asset_name' => $this->session->asset->name,
            'ended_at' => $this->session->ended_at ?? $this->session->end_datetime,
            'duration_minutes' => $this->session->actual_duration,
            'was_terminated' => $this->session->is_terminated,
            'audit_logs_count' => $this->terminationResults['audit_log_count'] ?? 0,
        ];
    }
}
