<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Salon;
use App\Models\Service;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerSchedule;
use App\Support\Tenant;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SeedSalonDemoData extends Command
{
    protected $signature = 'salon:seed-demo-data
        {salon : ID salonu}
        {--workers=3 : Liczba pracownikow}
        {--clients=20 : Liczba klientow}
        {--appointments=50 : Liczba wizyt}
        {--days=30 : Zakres dni dla generowanych wizyt}';

    protected $description = 'Tworzy dane demo dla wskazanego salonu bez wysylki SMS';

    public function handle(): int
    {
        $salon = Salon::find($this->argument('salon'));

        if (!$salon) {
            $this->error('Nie znaleziono salonu.');

            return self::FAILURE;
        }

        $workerCount = max(1, (int) $this->option('workers'));
        $clientCount = max(1, (int) $this->option('clients'));
        $appointmentCount = max(1, (int) $this->option('appointments'));
        $days = max(1, (int) $this->option('days'));

        DB::transaction(function () use ($salon, $workerCount, $clientCount, $appointmentCount, $days) {
            Tenant::set($salon->id);

            $workers = $this->resolveWorkers($salon, $workerCount);
            $services = $this->resolveServices($salon);
            $clients = $this->resolveClients($salon, $clientCount);
            $creatorId = User::query()->where('salon_id', $salon->id)->value('id');

            $this->createAppointments(
                salon: $salon,
                workers: $workers,
                services: $services,
                clients: $clients,
                appointmentCount: $appointmentCount,
                creatorId: $creatorId,
                days: $days,
            );

            Tenant::clear();
        });

        $this->info("Utworzono dane demo dla salonu #{$salon->id} ({$salon->name}).");

        return self::SUCCESS;
    }

    private function resolveWorkers(Salon $salon, int $count): Collection
    {
        $existing = Worker::query()->withoutGlobalScopes()->where('salon_id', $salon->id)->get();

        if ($existing->isNotEmpty()) {
            return $existing;
        }

        return $this->createWorkers($salon, $count);
    }

    private function createWorkers(Salon $salon, int $count): Collection
    {
        $names = [
            'Anna Stylistka',
            'Basia Kolorystka',
            'Karolina Fryzjerka',
            'Natalia Barberka',
            'Ola Beauty',
            'Patrycja Nails',
        ];
        $colors = ['#d97706', '#0f766e', '#be185d', '#4338ca', '#059669', '#b45309'];

        return collect(range(0, $count - 1))->map(function (int $index) use ($salon, $names, $colors) {
            $worker = Worker::create([
                'salon_id' => $salon->id,
                'name' => $names[$index] ?? fake()->name(),
                'active' => true,
                'color_hex' => $colors[$index % count($colors)],
            ]);

            foreach (range(1, 6) as $dayOfWeek) {
                WorkerSchedule::create([
                    'salon_id' => $salon->id,
                    'worker_id' => $worker->id,
                    'day_of_week' => $dayOfWeek,
                    'is_working' => true,
                    'start_time' => $dayOfWeek === 6 ? '09:00' : '09:00',
                    'end_time' => $dayOfWeek === 6 ? '14:00' : '17:00',
                ]);
            }

            WorkerSchedule::create([
                'salon_id' => $salon->id,
                'worker_id' => $worker->id,
                'day_of_week' => 0,
                'is_working' => false,
                'start_time' => null,
                'end_time' => null,
            ]);

            return $worker;
        });
    }

    private function resolveServices(Salon $salon): Collection
    {
        $existing = Service::query()->withoutGlobalScopes()->where('salon_id', $salon->id)->get();

        if ($existing->isNotEmpty()) {
            return $existing;
        }

        return $this->createServices($salon);
    }

    private function createServices(Salon $salon): Collection
    {
        $services = [
            ['name' => 'Strzyzenie damskie', 'duration_min' => 60, 'price' => 180],
            ['name' => 'Strzyzenie meskie', 'duration_min' => 45, 'price' => 120],
            ['name' => 'Koloryzacja', 'duration_min' => 120, 'price' => 320],
            ['name' => 'Modelowanie', 'duration_min' => 45, 'price' => 110],
            ['name' => 'Pielegnacja', 'duration_min' => 30, 'price' => 90],
        ];

        return collect($services)->map(fn (array $service) => Service::create([
            'salon_id' => $salon->id,
            'name' => $service['name'],
            'duration_min' => $service['duration_min'],
            'price' => $service['price'],
            'active' => true,
        ]));
    }

    private function resolveClients(Salon $salon, int $count): Collection
    {
        $existing = Client::query()->withoutGlobalScopes()->where('salon_id', $salon->id)->get();

        if ($existing->isNotEmpty()) {
            return $existing;
        }

        return $this->createClients($salon, $count);
    }

    private function createClients(Salon $salon, int $count): Collection
    {
        $faker = fake('pl_PL');
        $faker->unique(true);

        return collect(range(1, $count))->map(function () use ($salon, $faker) {
            $name = $faker->name();

            return Client::create([
                'salon_id' => $salon->id,
                'name' => $name,
                'phone' => '+48'.$faker->unique()->numerify('#########'),
                'email' => Str::of($name)->lower()->replaceMatches('/[^a-z0-9]+/i', '.')->trim('.').$faker->unique()->numberBetween(1, 999).'@example.test',
                'notes' => fake()->optional()->sentence(),
            ]);
        });
    }

    private function createAppointments(
        Salon $salon,
        Collection $workers,
        Collection $services,
        Collection $clients,
        int $appointmentCount,
        ?int $creatorId,
        int $days,
    ): void {
        $slots = $this->buildCandidateSlots($workers, $days);
        $occupied = [];
        $created = 0;

        foreach ($slots as $slot) {
            if ($created >= $appointmentCount) {
                break;
            }

            /** @var Worker $worker */
            $worker = $slot['worker'];
            /** @var Service $service */
            $service = $services->random();
            /** @var Client $client */
            $client = $clients->random();
            $startsAt = $slot['starts_at'];
            $endsAt = $startsAt->addMinutes((int) $service->duration_min);

            $workerOccupied = $occupied[$worker->id] ?? [];
            $hasOverlap = collect($workerOccupied)->contains(function (array $interval) use ($startsAt, $endsAt) {
                return $startsAt->lt($interval['end']) && $endsAt->gt($interval['start']);
            });

            if ($hasOverlap) {
                continue;
            }

            Appointment::create([
                'salon_id' => $salon->id,
                'worker_id' => $worker->id,
                'client_id' => $client->id,
                'service_id' => $service->id,
                'starts_at' => $startsAt,
                'duration_min' => $service->duration_min,
                'status' => fake()->randomElement(['planned', 'planned', 'confirmed']),
                'price_charged' => $service->price,
                'notes' => fake()->optional()->sentence(),
                'created_by_user_id' => $creatorId,
            ]);

            $occupied[$worker->id][] = [
                'start' => $startsAt,
                'end' => $endsAt,
            ];
            $created++;
        }
    }

    private function buildCandidateSlots(Collection $workers, int $days): Collection
    {
        $slots = collect();
        $startDate = CarbonImmutable::now()->startOfDay();

        foreach (range(0, max(0, $days - 1)) as $dayOffset) {
            $day = $startDate->addDays($dayOffset);

            if ($day->dayOfWeek === 0) {
                continue;
            }

            $hours = $day->dayOfWeek === 6 ? range(9, 13) : range(9, 16);

            foreach ($workers as $worker) {
                foreach ($hours as $hour) {
                    $slots->push([
                        'worker' => $worker,
                        'starts_at' => $day->setTime($hour, fake()->randomElement([0, 15, 30])),
                    ]);
                }
            }
        }

        return $slots->shuffle();
    }
}
