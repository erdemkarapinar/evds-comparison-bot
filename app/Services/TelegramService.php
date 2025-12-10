<?php

namespace App\Services;

use GuzzleHttp\Client;

class TelegramService
{
    protected Client $client;
    protected string $token;
    protected string $chatId;

    public function __construct()
    {
        $this->client = new Client(['timeout' => 10]);
        $this->token = config('services.telegram.token') ?? env('TELEGRAM_BOT_TOKEN');
        $this->chatId = config('services.telegram.chat_id') ?? env('TELEGRAM_CHAT_ID');
    }

    public function sendMessage(string $text)
    {
        $url = "https://api.telegram.org/bot{$this->token}/sendMessage";
        $this->client->post($url, [
            'json' => [
                'chat_id' => $this->chatId,
                'text' => $text,
                'parse_mode' => 'HTML'
            ],
        ]);
    }
}