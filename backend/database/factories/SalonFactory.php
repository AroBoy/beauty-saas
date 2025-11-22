<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Salon>
 */
class SalonFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company().' Salon',
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'default_visit_length_min' => 30,
            'sms_sender' => 'SALON',
            'sms_reminder_hours' => 24,
        ];
    }
}
