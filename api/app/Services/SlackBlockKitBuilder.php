<?php

// TODO: RE-WRITE THIS LATER

namespace App\Services;

use App\Models\Request;

class SlackBlockKitBuilder
{
    public function headerMessage(string $message, array $accessories = []): array
    {
        $block = [
            'type' => 'section',
            'text' => [
                'type' => 'mrkdwn',
                'text' => $message,
            ],
        ];
        if (!empty($accessories)) {
            $block['accessory'] = $accessories;
        }
        return $block;
    }

    public function button(string $text, string $url): array
    {
        return [
            'type' => 'button',
            'text' => [
                'type' => 'plain_text',
                'text' => $text,
                'emoji' => true,
            ],
            'url' => $url,
            'action_id' => 'button-action',
        ];
    }

    public function divider(): array
    {
        return [
            'type' => 'divider',
        ];
    }

    public function sectionMessage(string $message): array
    {
        return [
            'type' => 'section',
            'text' => [
                'type' => 'mrkdwn',
                'text' => $message,
            ],
        ];
    }

    public function contextMessage(string $message): array
    {
        return [
            'type' => 'context',
            'elements' => [
                [
                    'type' => 'mrkdwn',
                    'text' => $message,
                ],
            ],
        ];
    }

    public static function buildRequestNotificationBlocks(Request $request, string $message = 'You have a new request'): array
    {
        $blocks = [
            self::headerMessage($message, [self::button('View Request', config('pam.app.web_url').'/requests/'.$request->id)]),
            self::headerMessage('Request Summary'),
            self::divider(),
            self::sectionMessage(sprintf(
                "*Requester*: %s (%s)\n\n*Asset:* %s\n\n*Access Period:* %s - %s (%s)\n\n*Duration:* %s",
                $request->requester->name,
                $request->requester->email,
                $request->asset->name,
                $request->start_time->format('M d, Y H:i'),
                $request->end_time->format('M d, Y H:i'),
                $request->start_time->format('T P'),
                $request->start_time->diffForHumans($request->end_time, true)
            )),
            self::divider(),
            self::sectionMessage(sprintf(
                "*Reason:* %s\n\n*Scope:* %s\n\n*Risk Rating:* %s",
                $request->reason ?: 'Not specified',
                'All', // You might want to make this dynamic based on your request model
                $request->risk_rating ?? 'Medium'
            )),
        ];

        // Add context if request has reason
        if ($request->reason) {
            $blocks[] = [
                'type' => 'context',
                'elements' => [
                    [
                        'type' => 'mrkdwn',
                        'text' => "⚠️ *Request Reason*\n".$request->reason,
                    ],
                ],
            ];
        }

        $blocks[] = [
            'type' => 'header',
            'text' => [
                'type' => 'plain_text',
                'text' => 'Request Details',
                'emoji' => true,
            ],
        ];

        $blocks[] = [
            'type' => 'divider',
        ];

        $blocks[] = [
            'type' => 'section',
            'text' => [
                'type' => 'mrkdwn',
                'text' => sprintf(
                    "*Reason:* %s\n\n*Scope:* %s\n\n*Risk Rating:* %s",
                    $request->reason ?: 'Not specified',
                    'All', // You might want to make this dynamic based on your request model
                    $request->risk_rating ?? 'Medium'
                ),
            ],
        ];

        return $blocks;
    }

    public static function buildSessionNotificationBlocks($session, string $message = 'Session notification'): array
    {
        // Similar structure for session notifications
        return [
            [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => $message,
                ],
            ],
        ];
    }
}

// {
// 	"blocks": [
// 		{
// 			"type": "section",
// 			"text": {
// 				"type": "mrkdwn",
// 				"text": "You have a new request"
// 			},
// 			"accessory": {
// 				"type": "button",
// 				"text": {
// 					"type": "plain_text",
// 					"text": "View Request",
// 					"emoji": true
// 				},
// 				"url": "https://google.com",
// 				"action_id": "button-action"
// 			}
// 		},
// 		{
// 			"type": "header",
// 			"text": {
// 				"type": "plain_text",
// 				"text": "Request Summary",
// 				"emoji": true
// 			}
// 		},
// 		{
// 			"type": "divider"
// 		},
// 		{
// 			"type": "section",
// 			"text": {
// 				"type": "mrkdwn",
// 				"text": "*Requester*: Hean Luen (heanluen@gmail.com)\n\n*Asset:* Sayyam Production DB\n\n*Access Period:* Oct 05, 2025 21:13 - Oct 05, 2025 22:14 (GMT+08:00 Asia/Singapore)\n\n*Duration:* 1 hour 1 minute"
// 			}
// 		},
// 		{
// 			"type": "context",
// 			"elements": [
// 				{
// 					"type": "mrkdwn",
// 					"text": "⚠️ *Sensitive Data Warning*\nFix mismatched account balances discovered during financial reconciliation between the ledger and payments tables after a system upgrade."
// 				}
// 			]
// 		},
// 		{
// 			"type": "header",
// 			"text": {
// 				"type": "plain_text",
// 				"text": "Request Details",
// 				"emoji": true
// 			}
// 		},
// 		{
// 			"type": "divider"
// 		},
// 		{
// 			"type": "section",
// 			"text": {
// 				"type": "mrkdwn",
// 				"text": "*Reason:* Fix mismatched account balances discovered during financial reconciliation between the ledger and payments tables after a system upgrade.\n\n*Scope:* All\n\n*Risk Rating:* High"
// 			}
// 		}
// 	]
// }
