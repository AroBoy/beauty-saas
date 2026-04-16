<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Salon;
use App\Models\Service;
use App\Models\SmsJob;
use App\Models\Worker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DispatchSmsJobsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sends_due_sms_jobs_during_command_execution(): void
    {
        Http::fake([
            '*' => Http::response('provider-message-id', 200),
        ]);

        config()->set('sms.driver', 'smsapi');
        config()->set('sms.smsapi.token', 'test-token');
        config()->set('sms.smsapi.endpoint', 'https://api.smsapi.pl/sms.do');

        $salon = Salon::create([
            'name' => 'Test Salon',
            'sms_sender' => 'TEST',
        ]);

        $worker = Worker::create([
            'salon_id' => $salon->id,
            'name' => 'Worker',
        ]);

        $client = Client::create([
            'salon_id' => $salon->id,
            'name' => 'Client',
            'phone' => '+48518447831',
        ]);

        $service = Service::create([
            'salon_id' => $salon->id,
            'name' => 'Strzyzenie',
            'duration_min' => 60,
        ]);

        $appointment = Appointment::create([
            'salon_id' => $salon->id,
            'worker_id' => $worker->id,
            'client_id' => $client->id,
            'service_id' => $service->id,
            'starts_at' => now()->addDay(),
            'duration_min' => 60,
            'status' => 'planned',
        ]);

        $smsJob = SmsJob::create([
            'salon_id' => $salon->id,
            'appointment_id' => $appointment->id,
            'to_phone' => $client->phone,
            'type' => 'booking_confirmation',
            'send_at' => now()->subMinute(),
            'status' => 'pending',
            'message_body' => 'Test SMS',
        ]);

        $this->artisan('sms:dispatch-due')
            ->expectsOutput('Wysłano lub obsłużono 1 SMS.')
            ->assertSuccessful();

        $smsJob->refresh();

        $this->assertSame('sent', $smsJob->status);
        $this->assertNotNull($smsJob->sent_at);

        Http::assertSentCount(1);
        Http::assertSent(function ($request) use ($client) {
            return $request->url() === 'https://api.smsapi.pl/sms.do'
                && $request['to'] === $client->phone
                && $request['message'] === 'Test SMS'
                && $request['from'] === 'TEST';
        });
    }
}
