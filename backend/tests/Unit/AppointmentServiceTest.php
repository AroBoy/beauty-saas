<?php

namespace Tests\Unit;

use App\Models\Appointment;
use App\Models\Salon;
use App\Models\Worker;
use App\Services\AppointmentService;
use ReflectionMethod;
use Tests\TestCase;

class AppointmentServiceTest extends TestCase
{
    public function test_reminder_body_is_short_ascii_friendly_message(): void
    {
        $salon = new Salon([
            'name' => 'Demo Salon',
        ]);

        $worker = new Worker([
            'name' => 'Basia',
        ]);

        $appointment = new Appointment([
            'starts_at' => '2026-04-17 11:00:00',
        ]);
        $appointment->setRelation('salon', $salon);
        $appointment->setRelation('worker', $worker);

        $method = new ReflectionMethod(AppointmentService::class, 'reminderBody');
        $method->setAccessible(true);

        $message = $method->invoke(new AppointmentService(), $appointment);

        $this->assertSame('Przypomnienie o wizycie: 2026-04-17 11:00, Demo Salon.', $message);
        $this->assertLessThanOrEqual(70, strlen($message));
    }

    public function test_reminder_body_trims_long_salon_names_to_fit_sms_limit(): void
    {
        $salon = new Salon([
            'name' => 'Bardzo Dluga Nazwa Salonu Testowego Ktora Nie Powinna Przekroczyc Limitu',
        ]);

        $appointment = new Appointment([
            'starts_at' => '2026-04-17 11:00:00',
        ]);
        $appointment->setRelation('salon', $salon);

        $method = new ReflectionMethod(AppointmentService::class, 'reminderBody');
        $method->setAccessible(true);

        $message = $method->invoke(new AppointmentService(), $appointment);

        $this->assertLessThanOrEqual(70, strlen($message));
        $this->assertStringStartsWith('Przypomnienie o wizycie: 2026-04-17 11:00, ', $message);
        $this->assertStringEndsWith('.', $message);
    }
}
