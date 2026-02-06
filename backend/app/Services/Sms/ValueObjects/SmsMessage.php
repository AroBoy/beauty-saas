<?php

namespace App\Services\Sms\ValueObjects;

class SmsMessage
{
    public function __construct(
        public readonly string $to,
        public readonly string $body,
        public readonly ?string $from = null,
    ) {
    }
}
