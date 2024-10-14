<?php

use Google\Client;
use App\Models\Token;
use App\Models\Setting;

function send_notification22333($title, $body, $type, $image, $category, $city, $radio_station)
{
    $tokens = [];
    $tokens = Token::pluck('token');

    $project_id = Setting::select('message')->where('type', 'project_id')->pluck('message')->first();
    $url = 'https://fcm.googleapis.com/v1/projects/' . $project_id . '/messages:send';

    $access_token = getAccessToken();

    foreach ($tokens as $token) {
        $data = [
            "message" => [
                "token" => $token,
                "notification" => [
                    "title" => $title,
                    "body" => $body
                ],
                "android" => [
                    "notification"=> [
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    ],
                    "data" => [
                        "title" => $title,
                        "body" => $body,
                        "type" => $type,
                        "image" => $image,
                    ]
                ],
                "apns" => [
                    "headers" => [
                        "apns-priority" => "10" // Set APNs priority to 10 (high) for immediate delivery
                    ],
                    "payload" => [
                        "aps" => [
                            "alert" => [
                                "title" => $title,
                                "body" => $body
                            ],
                            "type" => $type,
                            "image" => $image,
                        ]
                    ]
                ]
            ]
        ];
        $encodedData = json_encode($data);

        $headers = [
            'Authorization: Bearer ' . $access_token,
            'Content-Type: application/json',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);

        // Execute post
        $result = curl_exec($ch);


        if ($result == FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
        // Close connection
        curl_close($ch);
    }

}
function getAccessToken()
{
    $file_name = Setting::select('value')->where('type', 'service_file')->pluck('value')->first();

    $file_path = base_path('public/storage/'. $file_name);

    $client = new Client();
    $client->setAuthConfig($file_path);
    $client->setScopes(['https://www.googleapis.com/auth/firebase.messaging']);
    $accessToken = $client->fetchAccessTokenWithAssertion()['access_token'];

    return $accessToken;
}
