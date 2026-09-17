<?php

namespace Lambda\Notify;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;

class Notify extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'notify';
    }

    static function createNotification($data)
    {
//        if (config('notify.provider') == 'socket') {
//            $client = new Client(new Version2X('http://202.21.110.214:3000', [
//                'headers' => [
//                    'X-My-Header: websocket',
//                ]
//            ]));
//            if ($client) {
//                $client->initialize();
//                $client->emit('notify', $data);
//                $client->close();
//            }
//        }

        $users = DB::table('users')
            ->whereIn('id', $data['users'])
            ->whereNotNull('token')
            ->select('token')
            ->get();

        $tokens = [];
        forEach ($users as $u) {
            if (!empty($u->token)) {
                $tokens[] = $u->token;
            }
        }

        $msg = [
            'message' => $data,
            'title' => $data['title'],
            'vibrate' => 1,
            'sound' => '/lambda/notification.mp3',
            'largeIcon' => 'http://tuushin.mn/assets/images/favicon.png',
            'smallIcon' => 'http://tuushin.mn/assets/images/favicon.png'
        ];

        self::sendNotification($tokens, $msg);

        $r = DB::table('notifications')->insertGetId([
            'title' => $data['title'],
            'link' => $data['link'],
            'body' => $data['body'],
            'sender' => auth()->id(),
            'created_at' => Carbon::now()
        ]);

        foreach ($data['users'] as $u) {
            DB::table('notification_status')->insert([
                'notif_id' => $r,
                'receiver_id' => strval($u),
                'seen' => false
            ]);
        }

//        if ($data['users'] == 'all') {
//            $users = DB::table('users')->select('id');
//            if ($data['role']) {
//                $users = $users->whereIn('role', [$data['role']])->get();
//            } else {
//                $users = $users->get();
//            }
//            foreach ($users as $u) {
//                $tbNotificationStatus->insert([
//                    'notif_id' => $r,
//                    'receiver_id' => $u->id,
//                    'seen' => false
//                ]);
//            }
//        } else {
//           );
//            }
//        }
    }

    static function sendNotification($receivers, $msg)
    {
        $fields = [
            'registration_ids' => $receivers,
            'data' => $msg
        ];

        $headers =
            [
                'Authorization: key=' . config('services.fcm.key', env('FCM_SERVER_KEY', 'AIzaSyDPHOFFQy7fWC14Vncn7sC7o0mEztpXzE4')),
                'Content-Type: application/json'
            ];

        $url = 'https://fcm.googleapis.com/fcm/send';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        curl_close($ch);
    }
}
