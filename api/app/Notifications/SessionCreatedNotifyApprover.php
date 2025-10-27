<?php

namespace App\Notifications;

use App\Models\Session;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class SessionCreatedNotifyApprover extends Notification implements ShouldQueue
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
        $channels = ['mail'];

        if (config('pam.notification.slack.enabled', false)) {
            $channels[] = 'slack';
        }

        return $channels;
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
            ->tag('session-created')
            ->subject('Session Created: '.$this->session->asset->name.' - '.$this->session->requester->name)
            ->markdown('mail.session.created.approver', [
                'session' => $this->session,
                'notifiable' => $notifiable,
                'url' => config('pam.app.web_url').'/sessions/'.$this->session->id,
            ]);
    }

    // public function toSlack(object $notifiable): SlackMessage
    // {
    //     return (new SlackMessage)
    //         ->from('Argus PAM')
    //         ->to(config('pam.notification.slack.channel.sessions'))
    //         ->content('🔵 Session Created: '.$this->session->asset->name)
    //         ->attachment(function ($attachment) {
    //             $attachment->title('Session Created', config('pam.app.web_url').'/sessions/'.$this->session->id)
    //                 ->content('A session has been created for *'.$this->session->asset->name.'* by *'.$this->session->requester->name.'*')
    //                 ->color('good')
    //                 ->markdown();
    //         });
    // }

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
