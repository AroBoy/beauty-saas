<?php

namespace Tests\Feature\Auth;

use App\Models\Salon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'company_name' => 'Test Salon',
            'company_address' => 'Testowa 1, Warszawa',
            'company_phone' => '+48123123123',
            'company_email' => 'biuro@test-salon.pl',
            'sms_sender' => 'TEST',
            'name' => 'Test User',
            'email' => 'admin@test-salon.pl',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $this->assertDatabaseHas(Salon::class, [
            'name' => 'Test Salon',
            'email' => 'biuro@test-salon.pl',
            'sms_sender' => 'TEST',
            'sms_reminder_hours' => 24,
        ]);
        $this->assertDatabaseHas(User::class, [
            'name' => 'Test User',
            'email' => 'admin@test-salon.pl',
            'role' => 'admin',
        ]);
        $response->assertRedirect(route('dashboard', absolute: false));
    }
}
