<?php

namespace Tests\Feature;

use App\Events\QueueUpdated;
use App\Models\Service;
use App\Models\Turn;
use App\TurnStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TurnQueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_kiosk_issues_sequential_turns_and_broadcasts_updates(): void
    {
        Event::fake([QueueUpdated::class]);

        $service = Service::factory()->create([
            'name' => 'Atención general',
            'prefix' => 'A',
            'average_minutes' => 5,
        ]);

        $this->post(route('turns.store'), ['service_id' => $service->id])
            ->assertRedirect();

        $first = Turn::query()->orderBy('id')->firstOrFail();

        $this->assertSame('A-001', $first->code);
        $this->assertSame(1, $first->number);
        $this->assertSame(TurnStatus::Waiting, $first->status);
        $this->assertTrue($first->issued_on->isToday());

        $this->post(route('turns.store'), ['service_id' => $service->id])->assertRedirect();

        $second = Turn::query()->orderByDesc('id')->firstOrFail();

        $this->assertSame('A-002', $second->code);
        $this->assertSame(2, $second->number);
        $this->assertSame(2, Turn::count());

        Event::assertDispatchedTimes(QueueUpdated::class, 2);
    }

    public function test_ticket_exposes_live_position_and_estimate(): void
    {
        $service = Service::factory()->create([
            'prefix' => 'A',
            'average_minutes' => 5,
        ]);

        Turn::factory()->create([
            'service_id' => $service->id,
            'number' => 1,
            'code' => 'A-001',
            'status' => TurnStatus::Waiting,
        ]);

        $second = Turn::factory()->create([
            'service_id' => $service->id,
            'number' => 2,
            'code' => 'A-002',
            'status' => TurnStatus::Waiting,
        ]);

        $this->get(route('turns.ticket', $second))->assertOk()->assertSee('A-002');

        $this->getJson(route('turns.status', $second))
            ->assertOk()
            ->assertJson([
                'code' => 'A-002',
                'status' => 'waiting',
                'is_waiting' => true,
                'waiting_ahead' => 1,
                'position' => 2,
                'estimated_minutes' => 5,
            ]);
    }

    public function test_turns_still_work_when_the_websocket_server_is_unavailable(): void
    {
        config([
            'broadcasting.default' => 'reverb',
            'broadcasting.connections.reverb.key' => 'test-key',
            'broadcasting.connections.reverb.secret' => 'test-secret',
            'broadcasting.connections.reverb.app_id' => 'test-app',
            'broadcasting.connections.reverb.options.host' => '127.0.0.1',
            'broadcasting.connections.reverb.options.port' => 1,
            'broadcasting.connections.reverb.options.scheme' => 'http',
            'broadcasting.connections.reverb.options.useTLS' => false,
        ]);

        $service = Service::factory()->create(['prefix' => 'A']);

        $this->post(route('turns.store'), ['service_id' => $service->id])
            ->assertRedirect();

        $this->assertDatabaseCount('turns', 1);
    }

    public function test_inactive_services_cannot_issue_turns(): void
    {
        $service = Service::factory()->create(['is_active' => false]);

        $this->post(route('turns.store'), ['service_id' => $service->id])
            ->assertSessionHasErrors('service_id');

        $this->assertDatabaseCount('turns', 0);
    }
}
