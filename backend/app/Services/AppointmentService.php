<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Salon;
use App\Models\Service;
use App\Models\SmsJob;
use App\Models\Worker;
use App\Support\Tenant;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentService
{
    /**
     * @param array{
     *     worker_id:int,
     *     client_id:int,
     *     service_id:int,
     *     starts_at:string|\DateTimeInterface,
     *     duration_min:int,
     *     status?:string,
     *     price_charged?:string|float|null,
     *     notes?:string|null,
     *     created_by_user_id?:int|null
     * } $data
     */
    public function create(array $data): Appointment
    {
        return DB::transaction(function () use ($data) {
            $startsAt = CarbonImmutable::parse($data['starts_at']);
            $duration = (int) $data['duration_min'];
            $salonId = $this->resolveSalonId($data['worker_id']);

            $this->assertWorkerAvailable($data['worker_id'], $startsAt, $duration);

            $appointment = Appointment::create([
                'salon_id' => $salonId,
                'worker_id' => $data['worker_id'],
                'client_id' => $data['client_id'],
                'service_id' => $data['service_id'],
                'starts_at' => $startsAt,
                'duration_min' => $duration,
                'status' => $data['status'] ?? 'planned',
                'price_charged' => $data['price_charged'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by_user_id' => $data['created_by_user_id'] ?? null,
            ]);

            $this->syncSmsJobs($appointment);

            return $appointment;
        });
    }

    /**
     * @param array{
     *     worker_id?:int,
     *     client_id?:int,
     *     service_id?:int,
     *     starts_at?:string|\DateTimeInterface,
     *     duration_min?:int,
     *     status?:string,
     *     price_charged?:string|float|null,
     *     notes?:string|null
     * } $data
     */
    public function update(Appointment $appointment, array $data): Appointment
    {
        return DB::transaction(function () use ($appointment, $data) {
            $startsAt = array_key_exists('starts_at', $data)
                ? CarbonImmutable::parse($data['starts_at'])
                : $appointment->starts_at;

            $duration = array_key_exists('duration_min', $data)
                ? (int) $data['duration_min']
                : $appointment->duration_min;

            $workerId = $data['worker_id'] ?? $appointment->worker_id;
            $salonId = $data['salon_id'] ?? $appointment->salon_id ?? $this->resolveSalonId($workerId);
            Tenant::set($salonId);

            $timeChanged = $startsAt->ne($appointment->starts_at) || $duration !== $appointment->duration_min || $workerId !== $appointment->worker_id;

            if ($timeChanged) {
                $this->assertWorkerAvailable($workerId, $startsAt, $duration, $appointment->id);
            }

            $appointment->fill(array_merge($data, ['salon_id' => $salonId]));
            $appointment->save();

            $this->syncSmsJobs($appointment);

            return $appointment;
        });
    }

    public function cancel(Appointment $appointment): void
    {
        DB::transaction(function () use ($appointment) {
            $appointment->update([
                'status' => 'cancelled',
            ]);

            $appointment->smsJobs()
                ->whereIn('status', ['pending'])
                ->update(['status' => 'cancelled']);
        });
    }

    protected function assertWorkerAvailable(int $workerId, CarbonImmutable $startsAt, int $durationMin, ?int $ignoreAppointmentId = null): void
    {
        $endsAt = $startsAt->addMinutes($durationMin);

        $overlapExists = Appointment::query()
            ->where('worker_id', $workerId)
            ->whereNotIn('status', ['cancelled'])
            ->when($ignoreAppointmentId, fn ($q) => $q->whereKeyNot($ignoreAppointmentId))
            ->where(function ($query) use ($startsAt, $endsAt) {
                $query->where('starts_at', '<', $endsAt)
                    ->whereRaw("starts_at + (duration_min || ' minutes')::interval > ?", [$startsAt]);
            })
            ->exists();

        if ($overlapExists) {
            throw ValidationException::withMessages([
                'starts_at' => 'Pracownik ma już wizytę w tym czasie.',
            ]);
        }
    }

    protected function syncSmsJobs(Appointment $appointment): void
    {
        Tenant::set($appointment->salon_id);

        $reminderHours = $appointment->salon->sms_reminder_hours ?? 24;
        $startsAt = CarbonImmutable::parse($appointment->starts_at);
        $reminderAt = $startsAt->subHours($reminderHours);

        // cancel pending jobs if status cancelled
        if ($appointment->status === 'cancelled') {
            $appointment->smsJobs()->where('status', 'pending')->update(['status' => 'cancelled']);

            return;
        }

        $appointment->smsJobs()
            ->where('type', 'reminder')
            ->where('status', 'pending')
            ->update(['send_at' => $reminderAt, 'message_body' => $this->reminderBody($appointment)]);

        // ensure confirmation exists
        $appointment->smsJobs()->firstOrCreate(
            ['type' => 'booking_confirmation', 'status' => 'pending'],
            [
                'salon_id' => Tenant::id(),
                'to_phone' => $appointment->client->phone ?? '',
                'send_at' => now(),
                'message_body' => $this->confirmationBody($appointment),
            ]
        );

        // ensure reminder exists
        $appointment->smsJobs()->updateOrCreate(
            ['type' => 'reminder', 'status' => 'pending'],
            [
                'salon_id' => Tenant::id(),
                'to_phone' => $appointment->client->phone ?? '',
                'send_at' => $reminderAt,
                'message_body' => $this->reminderBody($appointment),
            ]
        );
    }

    protected function confirmationBody(Appointment $appointment): string
    {
        $salonName = $appointment->salon->name;
        $starts = $appointment->starts_at->format('Y-m-d H:i');
        $worker = $appointment->worker?->name;

        return trim("Potwierdzenie wizyty: {$starts} {$salonName}" . ($worker ? " / {$worker}" : ''));
    }

    protected function reminderBody(Appointment $appointment): string
    {
        $salonName = $appointment->salon->name ?? 'Studio Fryzur';
        $date = $appointment->starts_at->format('Y-m-d');
        $time = $appointment->starts_at->format('H:i');

        return "{$salonName} przypomina o wizycie w dniu {$date} o godz {$time}\nPROSZĘ O POTWIERDZENIE WIZYTY";
    }

    protected function resolveSalonId(int $workerId): int
    {
        if (Tenant::has()) {
            return Tenant::id();
        }

        $salonId = Worker::query()->whereKey($workerId)->value('salon_id');

        if (!$salonId) {
            throw ValidationException::withMessages([
                'worker_id' => 'Nie znaleziono salonu dla pracownika.',
            ]);
        }

        Tenant::set($salonId);

        return $salonId;
    }
}
