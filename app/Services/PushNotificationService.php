<?php

namespace App\Services;

use App\Models\User;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use App\Models\PushNotification;
use App\Models\PushNotificationMsg;


class PushNotificationService
{
    public static function sendNotification(User $user, $title, $message, $url = '/')
    {
        $auth = [
            'VAPID' => [
                'subject' => 'https://sicpro.test/', // can be a mailto: or your website address
                'publicKey' => config('notifications.push_notification_public_key'), // (recommended) uncompressed public key P-256 encoded in Base64-URL
                'privateKey' => config('notifications.push_notification_private_key'), // (recommended) in fact the secret multiplier of the private key encoded in Base64-URL
            ],
        ];


        $webPush = new WebPush($auth);

        // Construct the payload with the logo
        $payload = json_encode([
            'title' => $title,
            'body' => $message,
            'url' => $url,
        ]);

        $msg = new PushNotificationMsg();
        $msg->title = $title;
        $msg->body = $message;
        $msg->url = $url;
        $msg->save();

        $notifications = PushNotification::all();

        foreach ($notifications as $notification) {
            $webPush->sendOneNotification(
                Subscription::create($notification['subscriptions']),
                $payload,
                ['TTL' => 5000]
            );
        }
    }
}
