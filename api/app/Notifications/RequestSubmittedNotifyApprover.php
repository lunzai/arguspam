<?php

namespace App\Notifications;

use App\Models\Request;
use App\Services\SlackBlockKitBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class RequestSubmittedNotifyApprover extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected Request $request) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['mail'];

        // if (config('pam.notification.slack.enabled', false)) {
        //     $channels[] = 'slack';
        // }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->metadata('org', $this->request->org_id)
            ->metadata('asset', $this->request->asset_id)
            ->tag('request')
            ->tag('request-submitted')
            ->subject('Request Awaiting Approval: '.$this->request->asset->name)
            ->markdown('mail.request.submitted.approver', [
                'request' => $this->request,
                'notifiable' => $notifiable,
                'url' => config('pam.app.web_url').'/requests/'.$this->request->id,
            ]);
    }

    // public function toSlack(object $notifiable): SlackMessage
    // {
    //     $blocks = SlackBlockKitBuilder::buildRequestNotificationBlocks(
    //         $this->request,
    //         'You have a new request awaiting approval'
    //     );

    //     $template = json_encode(['blocks' => $blocks]);

    //     return (new SlackMessage)
    //         ->from('Argus PAM')
    //         ->to(config('pam.notification.slack.channel.requests'))
    //         ->usingBlockKitTemplate($template);
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
