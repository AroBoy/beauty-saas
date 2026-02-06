<?php

namespace App\Http\Controllers;

use App\Http\Requests\AppointmentRequest;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Service;
use App\Models\Worker;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function __construct(private readonly AppointmentService $service)
    {
    }

    public function index(Request $request): View
    {
        $date = $request->date ? now()->parse($request->date) : now();

        $appointments = Appointment::with(['client', 'worker', 'service'])
            ->whereDate('starts_at', $date->toDateString())
            ->orderBy('starts_at')
            ->get();

        $workers = Worker::orderBy('name')->get();

        return view('appointments.index', [
            'appointments' => $appointments,
            'workers' => $workers,
            'date' => $date->toDateString(),
            'clients' => Client::orderBy('name')->get(),
            'services' => Service::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return $this->formView(new Appointment());
    }

    public function store(AppointmentRequest $request)
    {
        $this->service->create($request->validated());

        if ($request->expectsJson()) {
            return response()->noContent();
        }

        return redirect()->route('appointments.index')->with('status', 'Wizyta dodana.');
    }

    public function edit(Appointment $appointment): View
    {
        return $this->formView($appointment);
    }

    public function update(AppointmentRequest $request, Appointment $appointment)
    {
        $this->service->update($appointment, $request->validated());

        if ($request->expectsJson()) {
            return response()->noContent();
        }

        return redirect()->route('appointments.index')->with('status', 'Wizyta zaktualizowana.');
    }

    public function destroy(Request $request, Appointment $appointment): Response|RedirectResponse|JsonResponse
    {
        $appointment->delete();

        if ($request->expectsJson()) {
            return response()->noContent();
        }

        return redirect()->route('appointments.index')->with('status', 'Wizyta anulowana.');
    }

    public function resources(): array
    {
        return Worker::orderBy('name')
            ->get()
            ->map(fn (Worker $w) => [
                'id' => (string) $w->id,
                'title' => $w->name,
                'eventColor' => $w->color_hex ?: null,
            ])
            ->toArray();
    }

    public function events(Request $request): array
    {
        $start = $request->date ? Carbon::parse($request->date)->startOfDay() : Carbon::today();
        $end = (clone $start)->endOfDay();

        return Appointment::with(['client', 'worker', 'service'])
            ->whereBetween('starts_at', [$start, $end])
            ->where('status', '!=', 'cancelled')
            ->get()
            ->map(function (Appointment $a) {
                $end = (clone $a->starts_at)->addMinutes($a->duration_min);

                return [
                    'id' => (string) $a->id,
                    'resourceId' => (string) $a->worker_id,
                    'title' => ($a->client?->name ?? 'Klient') . ' — ' . ($a->service?->name ?? ''),
                    'start' => $a->starts_at->toIso8601String(),
                    'end' => $end->toIso8601String(),
                    'editable' => true,
                    'extendedProps' => [
                        'status' => $a->status,
                        'price' => $a->price_charged,
                        'duration_min' => $a->duration_min,
                        'service_id' => $a->service_id,
                        'client_id' => $a->client_id,
                        'client_name' => $a->client?->name,
                    ],
                ];
            })
            ->toArray();
    }

    public function move(Request $request, Appointment $appointment): Response
    {
        $data = $request->validate([
            'worker_id' => ['required', 'integer', 'exists:workers,id'],
            'starts_at' => ['required', 'date'],
            'duration_min' => ['required', 'integer', 'min:1'],
        ]);

        $this->service->update($appointment, $data);

        return response()->noContent();
    }

    protected function formView(Appointment $appointment): View
    {
        return view('appointments.form', [
            'appointment' => $appointment,
            'workers' => Worker::orderBy('name')->get(),
            'clients' => Client::orderBy('name')->get(),
            'services' => Service::orderBy('name')->get(),
        ]);
    }
}
