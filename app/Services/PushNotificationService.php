<?php

namespace App\Services;

use Throwable;
use App\Models\User;
use Minishlink\WebPush\WebPush;
use App\Models\PushNotification;
use App\Models\PushNotificationMsg;
use Minishlink\WebPush\Subscription;


class PushNotificationService
{
    public static function sendNotification(User $user, $title, $message, $url = '/')
    {
        try {
            $auth = [
                'VAPID' => [
                    'subject' => url('/'), // can be a mailto: or your website address
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

            $msg = PushNotificationMsg([
                'title' => $title,
                'body' => $message,
                'url' => $url,
            ]);

            $notifications = PushNotification::whereHas('user', function ($query) {
                $query->whereHas('roles', function ($roleQuery) {
                    $roleQuery->where('name', 'Administrador')
                        ->orWhere('name', 'Gerencial')
                        ->orWhere('name', 'Administrativo');
                });
            })->get();

            foreach ($notifications as $notification) {
                $webPush->sendOneNotification(
                    Subscription::create($notification['subscriptions']),
                    $payload,
                    ['TTL' => 5000]
                );
            }
        } catch (Throwable $e) {
        }
    }
}
