<?php

namespace App\Console\Commands;

use App\Jobs\SendSmsJob;
use App\Models\SmsJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

class DispatchSmsJobs extends Command
{
    protected $signature = 'sms:dispatch-due';

    protected $description = 'Wyślij SMS-y, których czas nadszedł';

    public function handle(): int
    {
        $due = SmsJob::query()
            ->where('status', 'pending')
            ->where('send_at', '<=', now())
            ->orderBy('send_at')
            ->limit(50)
            ->pluck('id');

        if ($due->isEmpty()) {
            $this->info('Brak SMS-ów do wysłania.');

            return self::SUCCESS;
        }

        Bus::batch(
            $due->map(fn (int $id) => new SendSmsJob($id))->all()
        )->name('sms-dispatch')->dispatch();

        $this->info("Zlecono wysyłkę {$due->count()} SMS.");

        return self::SUCCESS;
    }
}
