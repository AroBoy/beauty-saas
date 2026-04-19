<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Salon;
use App\Models\Service;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeedSalonDemoDataCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_demo_data_for_selected_salon(): void
    {
        $salon = Salon::factory()->create([
            'name' => 'SalonTest1',
        ]);

        User::factory()->for($salon)->create([
            'role' => 'admin',
        ]);

        $this->artisan('salon:seed-demo-data', [
            'salon' => $salon->id,
            '--workers' => 3,
            '--clients' => 20,
            '--appointments' => 50,
        ])->assertSuccessful();

        $this->assertSame(3, Worker::query()->where('salon_id', $salon->id)->count());
        $this->assertSame(20, Client::query()->where('salon_id', $salon->id)->count());
        $this->assertSame(5, Service::query()->where('salon_id', $salon->id)->count());
        $this->assertSame(50, Appointment::query()->where('salon_id', $salon->id)->count());
        $this->assertSame(21, WorkerSchedule::query()->where('salon_id', $salon->id)->count());
    }

    public function test_it_can_add_appointments_within_specific_day_range_for_existing_salon_data(): void
    {
        $salon = Salon::factory()->create();
        User::factory()->for($salon)->create([
            'role' => 'admin',
        ]);

        $this->artisan('salon:seed-demo-data', [
            'salon' => $salon->id,
            '--workers' => 3,
            '--clients' => 20,
            '--appointments' => 10,
            '--days' => 14,
        ])->assertSuccessful();

        $this->artisan('salon:seed-demo-data', [
            'salon' => $salon->id,
            '--workers' => 1,
            '--clients' => 1,
            '--appointments' => 40,
            '--days' => 14,
        ])->assertSuccessful();

        $appointments = Appointment::query()->where('salon_id', $salon->id)->get();

        $this->assertSame(50, $appointments->count());
        $this->assertTrue($appointments->every(fn ($appointment) => $appointment->starts_at->lt(now()->addDays(14)->endOfDay())));
    }
}
