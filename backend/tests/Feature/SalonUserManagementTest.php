<?php

namespace Tests\Feature;

use App\Models\Salon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalonUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_users_page_and_create_operator(): void
    {
        $salon = Salon::factory()->create();
        $admin = User::factory()->admin()->create([
            'salon_id' => $salon->id,
        ]);

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Operator Test',
                'email' => 'operator@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'salon_id' => $salon->id,
            'name' => 'Operator Test',
            'email' => 'operator@example.com',
            'role' => 'operator',
        ]);
    }

    public function test_operator_cannot_access_users_page_or_settings(): void
    {
        $operator = User::factory()->operator()->create();

        $this->actingAs($operator)
            ->get(route('users.index'))
            ->assertForbidden();

        $this->actingAs($operator)
            ->get(route('settings.edit'))
            ->assertForbidden();
    }
}
