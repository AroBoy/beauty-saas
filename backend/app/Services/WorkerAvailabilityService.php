<?php

namespace App\Services;

use App\Models\Worker;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class WorkerAvailabilityService
{
    public function validate(Worker $worker, CarbonImmutable $startsAt, int $durationMin): ?string
    {
        if (!$worker->active) {
            return 'Pracownik jest nieaktywny.';
        }

        $endsAt = $startsAt->addMinutes($durationMin);

        if (!$startsAt->isSameDay($endsAt->subSecond())) {
            return 'Wizyta musi mieścić się w jednym dniu pracy.';
        }

        $schedule = $this->scheduleForDay($worker, $startsAt);

        if (!$schedule['is_working']) {
            return 'Pracownik ma dzień wolny.';
        }

        $workStart = $startsAt->setTimeFromTimeString($schedule['start_time']);
        $workEnd = $startsAt->setTimeFromTimeString($schedule['end_time']);

        if ($startsAt->lt($workStart) || $endsAt->gt($workEnd)) {
            return sprintf(
                'Pracownik przyjmuje tylko w godzinach %s-%s.',
                substr($schedule['start_time'], 0, 5),
                substr($schedule['end_time'], 0, 5)
            );
        }

        $hasTimeOff = $worker->timeOffs()
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->exists();

        if ($hasTimeOff) {
            return 'Pracownik jest niedostępny w tym czasie.';
        }

        return null;
    }

    public function scheduleForDay(Worker $worker, CarbonImmutable $date): array
    {
        $dayOfWeek = (int) $date->dayOfWeekIso;
        $schedule = $worker->relationLoaded('schedules')
            ? $worker->schedules->firstWhere('day_of_week', $dayOfWeek)
            : $worker->schedules()->where('day_of_week', $dayOfWeek)->first();

        if (!$schedule) {
            return [
                'is_working' => true,
                'start_time' => '07:00:00',
                'end_time' => '22:00:00',
            ];
        }

        return [
            'is_working' => (bool) $schedule->is_working,
            'start_time' => $schedule->start_time ?? '07:00:00',
            'end_time' => $schedule->end_time ?? '22:00:00',
        ];
    }

    public function unavailableCalendarEvents(Collection $workers, CarbonImmutable $day): array
    {
        $calendarStart = $day->setTime(6, 0);
        $calendarEnd = $day->setTime(22, 0);
        $events = [];

        foreach ($workers as $worker) {
            $schedule = $this->scheduleForDay($worker, $day);

            if (!$schedule['is_working']) {
                $events[] = $this->backgroundEvent($worker->id, $calendarStart, $calendarEnd);
            } else {
                $workStart = $day->setTimeFromTimeString($schedule['start_time']);
                $workEnd = $day->setTimeFromTimeString($schedule['end_time']);

                if ($workStart->gt($calendarStart)) {
                    $events[] = $this->backgroundEvent($worker->id, $calendarStart, $workStart);
                }

                if ($workEnd->lt($calendarEnd)) {
                    $events[] = $this->backgroundEvent($worker->id, $workEnd, $calendarEnd);
                }
            }

            $timeOffs = $worker->relationLoaded('timeOffs')
                ? $worker->timeOffs
                : $worker->timeOffs()->get();

            foreach ($timeOffs as $timeOff) {
                $startsAt = CarbonImmutable::parse($timeOff->starts_at)->max($calendarStart);
                $endsAt = CarbonImmutable::parse($timeOff->ends_at)->min($calendarEnd);

                if ($startsAt->lt($endsAt) && $startsAt->isSameDay($day)) {
                    $events[] = $this->backgroundEvent($worker->id, $startsAt, $endsAt, [
                        'title' => $timeOff->type,
                    ]);
                }
            }
        }

        return $events;
    }

    protected function backgroundEvent(int $workerId, CarbonImmutable $startsAt, CarbonImmutable $endsAt, array $extra = []): array
    {
        return array_merge([
            'resourceId' => (string) $workerId,
            'start' => $startsAt->toIso8601String(),
            'end' => $endsAt->toIso8601String(),
            'display' => 'background',
            'classNames' => ['fc-unavailable-slot'],
            'overlap' => false,
            'editable' => false,
        ], $extra);
    }
}
