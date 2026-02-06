<?php

namespace App\Jobs;

use App\Models\SmsJob;
use App\Services\Sms\SmsGateway;
use App\Services\Sms\ValueObjects\SmsMessage;
use App\Support\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public function __construct(public int $smsJobId)
    {
    }

    public function handle(SmsGateway $gateway): void
    {
        $smsJob = SmsJob::find($this->smsJobId);

        if (!$smsJob || $smsJob->status !== 'pending') {
            return;
        }

        if ($smsJob->salon_id) {
            Tenant::set($smsJob->salon_id);
        }

        $message = new SmsMessage(
            to: $smsJob->to_phone,
            body: $smsJob->message_body,
            from: $smsJob->salon->sms_sender ?? config('sms.from')
        );

        $result = $gateway->send($message);

        if ($result->success) {
            $smsJob->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } else {
            $smsJob->update([
                'status' => 'failed',
                'failure_reason' => $result->error,
            ]);
        }
    }
}
