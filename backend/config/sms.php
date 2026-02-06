<?php

return [
    'driver' => env('SMS_DRIVER', 'fake'), // fake | smsapi

    'from' => env('SMS_FROM', null),

    'smsapi' => [
        'token' => env('SMSAPI_TOKEN', ''),
        'endpoint' => env('SMSAPI_ENDPOINT', 'https://api.smsapi.pl/sms.do'),
    ],
];
