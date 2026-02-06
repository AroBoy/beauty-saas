<?php

namespace App\Services\Sms;

use App\Services\Sms\ValueObjects\SmsMessage;
use App\Services\Sms\ValueObjects\SmsResult;
use Illuminate\Support\Facades\Http;

class SmsapiGateway implements SmsGateway
{
    public function __construct(private readonly string $token, private readonly string $endpoint)
    {
    }

    public function send(SmsMessage $message): SmsResult
    {
        $payload = [
            'to' => $message->to,
            'message' => $message->body,
        ];

        if ($message->from) {
            $payload['from'] = $message->from;
        }

        $response = Http::asForm()
            ->withToken($this->token)
            ->post($this->endpoint, $payload);

        if ($response->successful()) {
            $id = $response->json('message_id') ?? $response->body();

            return SmsResult::ok($id);
        }

        $error = $response->json('error') ?? $response->body();

        return SmsResult::fail($error);
    }
}
