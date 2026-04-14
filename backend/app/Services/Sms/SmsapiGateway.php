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
            $body = trim($response->body());

            // SMSAPI can return transport-level 200 responses with an ERROR code in the body.
            if ($body !== '' && str_starts_with(mb_strtoupper($body), 'ERROR:')) {
                return SmsResult::fail($body);
            }

            $id = $response->json('message_id') ?? $body;

            return SmsResult::ok($id);
        }

        $error = $response->json('error') ?? $response->body();

        return SmsResult::fail($error);
    }
}
