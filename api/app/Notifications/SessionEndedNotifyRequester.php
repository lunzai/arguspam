<?php

namespace App\Notifications;

use App\Models\Session;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SessionEndedNotifyRequester extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected Session $session) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->metadata('org', $this->session->org_id)
            ->metadata('asset', $this->session->asset_id)
            ->tag('session')
            ->tag('session-ended')
            ->subject('Session Ended: '.$this->session->asset->name)
            ->markdown('mail.session.ended.requester', [
                'session' => $this->session,
                'notifiable' => $notifiable,
                'url' => config('pam.app.web_url').'/sessions/'.$this->session->id,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
