<?php

namespace VientoDigital\LaravelExpoPushNotifications\Channels;

use Illuminate\Notifications\Notification;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\Facades\Log;
use VientoDigital\LaravelExpoPushNotifications\ExpoPushNotifiable;

class ExpoPushNotificationChannel
{
    protected $http;
    protected $url;

    public function __construct(HttpClient $http)
    {
        $this->http = $http;
        $this->url = 'https://exp.host/--/api/v2/push/send';
    }

    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toExpo')) {
            throw new RuntimeException('Notification is missing toExpo method.');
        }
        // dump($notifiable->tokens());
        $data = $notification->toExpo($notifiable);
        // dump($data);
        // return;
        $this->sentTokens($notifiable, $notification, $data);
        ;
        if (!empty($notifiable->tokens()) && is_array($notifiable->tokens())) {
        }
    }

    private function sentTokens(ExpoPushNotifiable $notifiable, Notification $notification, array $data)
    {
        $tokens = collect($notifiable->tokens());
        $tokens->each(function ($to, $key) use ($notifiable, $notification,$data) {
            $data = $this->buildMessage($notification->toExpo($notifiable));
            $data['to'] = $to;
            Log::info(['data' => $data]);
            $this->http->request('POST', $this->url, ['json' => $data]);
        });
    }

    protected function buildMessage($data):array
    {
        $result = [
            'title' => $data->title,
            'body' => $data->body,
            'subtitle' => $data->subtitle,
            'priority' => $data->priority,
            'sound' => $data->sound,
            'badgeCount' => $data->badgeCount,
        ];
        return $result;
    }
}
