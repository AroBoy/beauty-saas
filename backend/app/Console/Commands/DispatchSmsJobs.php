<?php

namespace App\Console\Commands;

use App\Jobs\SendSmsJob;
use App\Models\SmsJob;
use Illuminate\Console\Command;

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

        foreach ($due as $id) {
            dispatch_sync(new SendSmsJob($id));
        }

        $this->info("Wysłano lub obsłużono {$due->count()} SMS.");

        return self::SUCCESS;
    }
}
