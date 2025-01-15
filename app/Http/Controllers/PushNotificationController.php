<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use App\Models\PushNotification;
use App\Models\PushNotificationMsg;

class PushNotificationController extends Controller
{
    //
    public function sendNotification(Request $request)
    {
        $auth = [
            'VAPID' => [
                'subject' => 'https://sicpro.test/', // can be a mailto: or your website address
                'publicKey' => env('PUSH_NOTIFICATION_PUBLIC_KEY'), // (recommended) uncompressed public key P-256 encoded in Base64-URL
                'privateKey' => env('PUSH_NOTIFICATION_PRIVATE_KEY'), // (recommended) in fact the secret multiplier of the private key encoded in Base64-URL
            ],
        ];

        $webPush = new WebPush($auth);

        // Construct the payload with the logo
        $payload = json_encode([
            'title' => $request->title,
            'body' => $request->body,
            'url' => './?id=' . $request->idOfProduct,
        ]);

        $msg = new PushNotificationMsg();
        $msg->title = $request->title;
        $msg->body = $request->body;
        $msg->url = $request->idOfProduct;
        $msg->save();

        $notifications = PushNotification::all();

        foreach ($notifications as $notification) {
            $webPush->sendOneNotification(
                Subscription::create($notification['subscriptions']),
                $payload,
                ['TTL' => 5000]
            );
        }

        return response()->json(['message' => 'send successfully'], 200);
    }

    public function saveSubscription(Request $request)
    {
        $data = json_decode($request->sub);
        $existEndpoint = PushNotification::where('endpoint', $data->endpoint)->exists();
        if (!$existEndpoint) {
            $items = new PushNotification();
            $items->user_id =  auth()->id();
            $items->subscriptions = $data;
            $items->endpoint = $data->endpoint;
            $items->save();
        }
        return response()->json(['message' => 'added successfully'], 200);
    }
}
