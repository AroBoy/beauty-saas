<?php

namespace Tests\Unit;

use App\Services\Sms\SmsapiGateway;
use App\Services\Sms\ValueObjects\SmsMessage;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SmsapiGatewayTest extends TestCase
{
    public function test_it_marks_error_body_as_failure_even_when_http_status_is_successful(): void
    {
        Http::fake([
            '*' => Http::response('ERROR:105', 200),
        ]);

        $gateway = new SmsapiGateway('token', 'https://api.smsapi.pl/sms.do');

        $result = $gateway->send(new SmsMessage(
            to: '+48518447831',
            body: 'Test SMS',
            from: 'TEST',
        ));

        $this->assertFalse($result->success);
        $this->assertSame('ERROR:105', $result->error);
    }

    public function test_it_marks_regular_success_response_as_success(): void
    {
        Http::fake([
            '*' => Http::response('123456', 200),
        ]);

        $gateway = new SmsapiGateway('token', 'https://api.smsapi.pl/sms.do');

        $result = $gateway->send(new SmsMessage(
            to: '+48518447831',
            body: 'Test SMS',
            from: 'TEST',
        ));

        $this->assertTrue($result->success);
        $this->assertSame('123456', $result->providerMessageId);

        Http::assertSent(function ($request) {
            return $request['normalize'] === 1;
        });
    }
}
