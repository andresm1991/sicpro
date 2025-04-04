<?php

namespace App\Services;

use Throwable;
use App\Models\User;
use Minishlink\WebPush\WebPush;
use App\Models\PushNotification;
use App\Models\PushNotificationMsg;
use App\Models\PushNotificationUser;
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

            $msg = PushNotificationMsg::create([
                'title' => $title,
                'body' => $message,
                'url' => $url,
            ])->id;

            if (auth()->user()->hasRole('Administrativo')) {
                $users = User::whereHas('roles', function ($q) {
                    $q->whereIn('name', ['Gerencial']);
                })->get();
            } elseif (auth()->user()->hasRole('Gerencial')) {
                $users = User::whereHas('roles', function ($q) {
                    $q->whereIn('name', ['Administrativo', 'Operativo']);
                })->get();
            } elseif (auth()->user()->hasRole('Operativo')) {
                $users = User::whereHas('roles', function ($q) {
                    $q->whereIn('name', ['Administrativo', 'Gerencial']);
                })->get();
            } else {
                $users = User::whereHas('roles', function ($q) {
                    $q->whereIn('name', ['Administrador']);
                })->get();
            }

            foreach ($users as $user) {
                $notification = PushNotificationUser::create([
                    'user_id' => $user->id,
                    'message_id' => $msg,
                    'leido' => false,
                ]);
                if ($user->notifications()->exists()) {
                    foreach ($user->notifications as $notification) {
                        $webPush->sendOneNotification(
                            Subscription::create($notification['subscriptions']),
                            $payload,
                            ['TTL' => 5000]
                        );
                    }
                }
            }
            /*$notifications = PushNotification::whereHas('user', function ($query) {
                $query->whereHas('roles', function ($roleQuery) {
                    $roleQuery->whereIn('name', ['Administrador', 'Gerencial', 'Administrativo']);
                });
            })->get();

            foreach ($notifications as $notification) {
                $webPush->sendOneNotification(
                    Subscription::create($notification['subscriptions']),
                    $payload,
                    ['TTL' => 5000]
                );
            }*/
        } catch (Throwable $e) {
            LogService::log('ERROR', 'Error al enviar notificación push', ['error_message' => $e->getMessage()]);
            return $e->getMessage();
        }
    }
}