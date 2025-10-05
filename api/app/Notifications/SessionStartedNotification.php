<?php

namespace App\Notifications;

use App\Models\Session;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SessionStartedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected Session $session)
    {
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
        $isRequester = $this->session->requester->is($notifiable);

        return (new MailMessage)
            ->subject('Database Session Started')
            ->greeting('Hello '.$notifiable->name)
            ->line($isRequester ?
                'Your database session has been started successfully.' :
                'A database session you approved has been started.'
            )
            ->line('Asset: '.$this->session->asset->name)
            ->line('Started Time: '.$this->session->checked_in_at->format('Y-m-d H:i:s'))
            ->line('Expires Time: '.$this->session->scheduled_end_datetime->format('Y-m-d H:i:s'))
            ->line('Remaining Duration: '.floor($this->session->getRemainingDuration() / 60).' minutes')
            ->line('Please remember to end your session when finished.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'session_started',
            'session_id' => $this->session->id,
            'asset_name' => $this->session->asset->name,
            'started_at' => $this->session->checked_in_at->format('Y-m-d H:i:s'),
            'expires_at' => $this->session->scheduled_end_datetime->format('Y-m-d H:i:s'),
        ];
    }
}
