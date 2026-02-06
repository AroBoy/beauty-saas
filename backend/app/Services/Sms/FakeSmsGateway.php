<?php

namespace App\Services\Sms;

use App\Services\Sms\ValueObjects\SmsMessage;
use App\Services\Sms\ValueObjects\SmsResult;
use Illuminate\Support\Facades\Log;

class FakeSmsGateway implements SmsGateway
{
    public function send(SmsMessage $message): SmsResult
    {
        Log::info('FAKE_SMS', [
            'to' => $message->to,
            'from' => $message->from,
            'body' => $message->body,
        ]);

        return SmsResult::ok('fake-message-id');
    }
}
