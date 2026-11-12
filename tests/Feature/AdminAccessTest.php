<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_the_agent_console(): void
    {
        $this->get(route('agent.index'))->assertRedirect(route('login'));
    }

    public function test_agents_cannot_access_administration(): void
    {
        $agent = User::factory()->create();

        $this->actingAs($agent)->get(route('admin.index'))->assertForbidden();
    }

    public function test_administrators_can_access_administration(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.index'))->assertOk();
    }

    public function test_administrator_creates_a_service(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.services.store'), [
                'name' => 'Registro civil',
                'prefix' => 'r',
                'description' => 'Actas y certificados.',
                'average_minutes' => 8,
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('services', [
            'name' => 'Registro civil',
            'prefix' => 'R',
            'is_active' => true,
        ]);
    }

    public function test_administrator_can_deactivate_a_service(): void
    {
        $admin = User::factory()->admin()->create();
        $service = Service::factory()->create(['prefix' => 'A']);

        $this->actingAs($admin)
            ->patch(route('admin.services.update', $service), [
                'name' => $service->name,
                'prefix' => $service->prefix,
                'description' => $service->description,
                'average_minutes' => $service->average_minutes,
            ])
            ->assertRedirect();

        $this->assertFalse($service->refresh()->is_active);
    }

    public function test_staff_can_log_in_with_seeded_style_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'agente@turnos.test',
            'password' => 'password',
        ]);

        $this->post(route('login.store'), [
            'email' => 'agente@turnos.test',
            'password' => 'password',
        ])->assertRedirect(route('agent.index'));

        $this->assertAuthenticatedAs($user);
    }
}
