<?php

namespace App\Services;

use App\Models\Setting;
use Google\Client;
use RuntimeException;
use Throwable;

class NotificationService {
    /**
     * @param array $registrationIDs
     * @param string|null $title
     * @param string|null $message
     * @param array $customBodyFields
     * @return false|mixed
     * @throws Throwable
     */
    public static function sendFcmNotification(array $registrationIDs, string|null $title = '', string|null $image = null, string|null $message = '', $type = "default", array $customBodyFields = []) {
        
        try {
            $project_id = Setting::select('value')->where('name', 'project_id')->first()->value;
            $url = 'https://fcm.googleapis.com/v1/projects/' . $project_id . '/messages:send';
            $access_token = self::getAccessToken();
            $headers = [
                'Authorization: Bearer ' . $access_token,
                'Content-Type: application/json',
            ];
            $result = [];
            $customFields = json_encode($customBodyFields);
            $notification_data = [
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                "title" => $title,
                "type" => $type,
                "image" => $image,
                "body"  => $message,
                "custom_body_fields" =>  $customFields
            ];
            foreach ($registrationIDs as $registrationID) {
               $data = [
                    "message"=>[
                        "token" => $registrationID,
                        "notification" => [
                            "title" => $title,
                             "body"  => $message,
                             "image" => $image,
                        ],
                        "data" => $notification_data
                    ]
                ];
                
                
                $encodedData = json_encode($data);
                
                // dd($encodedData);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);
                $result = curl_exec($ch);
                
                if ($result === false) {
                    throw new RuntimeException('Curl failed: ' . curl_error($ch));
                }
                curl_close($ch);
                // Log the result or handle it as needed
                error_log('FCM Response: ' . $result);
            }
            // dd($data);
            return $result;
        } catch (Throwable $th) {

            throw $th;
            error_log('Error sending FCM notification: ' . $th->getMessage());
            throw new RuntimeException($th);
        }
    }
    public static function getAccessToken()
    {
        $file_name = Setting::where('type', 'file')
                            ->where('name', 'service_file')
                            ->value('value');

        if (!$file_name) {
            throw new \Exception("Service account file name not found in settings");
        }

        $file_path = storage_path('app/public/' . $file_name);

        if (!file_exists($file_path)) {
            throw new \Exception("Service account file not found: $file_path");
        }

        $client = new Client();
        $client->setAuthConfig($file_path);
        $client->setScopes(['https://www.googleapis.com/auth/firebase.messaging']);
        $accessToken = $client->fetchAccessTokenWithAssertion()['access_token'];

        return $accessToken;
    }
}
