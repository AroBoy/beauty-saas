<?php

namespace App\Services\Sms;

use App\Services\Sms\ValueObjects\SmsMessage;
use App\Services\Sms\ValueObjects\SmsResult;

interface SmsGateway
{
    public function send(SmsMessage $message): SmsResult;
}
