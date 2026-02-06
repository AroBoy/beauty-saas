<?php

namespace App\Services\Sms\ValueObjects;

class SmsResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $providerMessageId = null,
        public readonly ?string $error = null
    ) {
    }

    public static function ok(?string $id = null): self
    {
        return new self(true, $id, null);
    }

    public static function fail(?string $error = null): self
    {
        return new self(false, null, $error);
    }
}
