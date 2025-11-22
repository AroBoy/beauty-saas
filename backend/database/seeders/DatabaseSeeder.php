<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Salon;
use App\Models\Service;
use App\Models\User;
use App\Models\Worker;
use App\Services\AppointmentService;
use App\Support\Tenant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $salon = Salon::factory()->create([
            'name' => 'Demo Salon',
            'phone' => '+48123123123',
            'email' => 'demo@salon.test',
            'sms_sender' => 'DEMO',
            'sms_reminder_hours' => 24,
        ]);

        Tenant::set($salon->id);

        $owner = User::factory()->for($salon)->create([
            'name' => 'Owner Demo',
            'email' => 'owner@demo.test',
            'role' => 'owner',
        ]);

        $worker = Worker::create([
            'salon_id' => $salon->id,
            'name' => 'Alex Stylista',
            'active' => true,
            'color_hex' => '#10b981',
        ]);

        $service = Service::create([
            'salon_id' => $salon->id,
            'name' => 'Strzyżenie męskie',
            'duration_min' => 45,
            'price' => 120,
            'active' => true,
        ]);

        $client = Client::create([
            'salon_id' => $salon->id,
            'name' => 'Jan Kowalski',
            'phone' => '+48555111222',
            'email' => 'jan.kowalski@example.com',
        ]);

        // demo wizyta na dziś za 2h
        app(AppointmentService::class)->create([
            'worker_id' => $worker->id,
            'client_id' => $client->id,
            'service_id' => $service->id,
            'starts_at' => now()->addHours(2),
            'duration_min' => 45,
            'status' => 'planned',
            'price_charged' => 120,
            'created_by_user_id' => $owner->id,
        ]);
    }
}
