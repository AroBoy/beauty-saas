<?php

namespace App\Http\Controllers;

use App\Http\Requests\WorkerRequest;
use App\Models\Worker;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WorkerController extends Controller
{
    public function index(): View
    {
        $workers = Worker::with(['schedules', 'timeOffs'])->orderBy('name')->get();

        return view('workers.index', compact('workers'));
    }

    public function create(): View
    {
        return view('workers.form', [
            'worker' => new Worker(),
            'scheduleRows' => $this->scheduleRows(),
            'timeOffRows' => [[]],
        ]);
    }

    public function store(WorkerRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $validated = $request->validated();
            $worker = Worker::create(collect($validated)->only(['name', 'user_id', 'active', 'color_hex'])->all());
            $this->syncWorkerAvailability($worker, $validated);
        });

        return redirect()->route('workers.index')->with('status', 'Pracownik utworzony.');
    }

    public function edit(Worker $worker): View
    {
        $worker->load(['schedules', 'timeOffs']);

        return view('workers.form', [
            'worker' => $worker,
            'scheduleRows' => $this->scheduleRows($worker),
            'timeOffRows' => $worker->timeOffs->map(fn ($timeOff) => [
                'starts_at' => optional($timeOff->starts_at)->format('Y-m-d\\TH:i'),
                'ends_at' => optional($timeOff->ends_at)->format('Y-m-d\\TH:i'),
                'type' => $timeOff->type,
                'note' => $timeOff->note,
            ])->values()->all() ?: [[]],
        ]);
    }

    public function update(WorkerRequest $request, Worker $worker): RedirectResponse
    {
        DB::transaction(function () use ($request, $worker) {
            $validated = $request->validated();
            $worker->update(collect($validated)->only(['name', 'user_id', 'active', 'color_hex'])->all());
            $this->syncWorkerAvailability($worker, $validated);
        });

        return redirect()->route('workers.index')->with('status', 'Pracownik zaktualizowany.');
    }

    public function destroy(Worker $worker): RedirectResponse
    {
        $worker->delete();

        return redirect()->route('workers.index')->with('status', 'Pracownik usunięty.');
    }

    protected function scheduleRows(?Worker $worker = null): array
    {
        $dayLabels = [
            1 => 'Poniedziałek',
            2 => 'Wtorek',
            3 => 'Środa',
            4 => 'Czwartek',
            5 => 'Piątek',
            6 => 'Sobota',
            7 => 'Niedziela',
        ];

        return collect($dayLabels)->map(function (string $label, int $day) use ($worker) {
            $schedule = $worker?->schedules->firstWhere('day_of_week', $day);

            return [
                'day' => $day,
                'label' => $label,
                'is_working' => $schedule?->is_working ?? true,
                'start_time' => $schedule?->start_time ? substr($schedule->start_time, 0, 5) : '07:00',
                'end_time' => $schedule?->end_time ? substr($schedule->end_time, 0, 5) : '22:00',
            ];
        })->values()->all();
    }

    protected function syncWorkerAvailability(Worker $worker, array $validated): void
    {
        $scheduleRows = collect($validated['schedule'] ?? [])
            ->map(function (array $row, int|string $day) use ($worker) {
                return [
                    'salon_id' => $worker->salon_id,
                    'worker_id' => $worker->id,
                    'day_of_week' => (int) $day,
                    'is_working' => (bool) ($row['is_working'] ?? false),
                    'start_time' => !empty($row['is_working']) ? ($row['start_time'] ?? null) : null,
                    'end_time' => !empty($row['is_working']) ? ($row['end_time'] ?? null) : null,
                ];
            })
            ->values();

        $worker->schedules()->delete();

        if ($scheduleRows->isNotEmpty()) {
            $worker->schedules()->createMany($scheduleRows->all());
        }

        $timeOffRows = collect($validated['time_offs'] ?? [])
            ->filter(fn (array $row) => !empty($row['starts_at']) && !empty($row['ends_at']))
            ->map(fn (array $row) => [
                'salon_id' => $worker->salon_id,
                'worker_id' => $worker->id,
                'starts_at' => $row['starts_at'],
                'ends_at' => $row['ends_at'],
                'type' => $row['type'] ?: 'custom',
                'note' => $row['note'] ?: null,
            ])
            ->values();

        $worker->timeOffs()->delete();

        if ($timeOffRows->isNotEmpty()) {
            $worker->timeOffs()->createMany($timeOffRows->all());
        }
    }
}
