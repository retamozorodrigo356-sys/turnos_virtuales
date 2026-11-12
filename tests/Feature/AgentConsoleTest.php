<?php

namespace Tests\Feature;

use App\Events\QueueUpdated;
use App\Events\TurnCalled;
use App\Models\Counter;
use App\Models\Service;
use App\Models\Turn;
use App\Models\User;
use App\TurnStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AgentConsoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_calls_the_oldest_waiting_turn(): void
    {
        Event::fake([TurnCalled::class, QueueUpdated::class]);

        $agent = User::factory()->create();
        $service = Service::factory()->create(['prefix' => 'A']);
        $counter = Counter::factory()->create(['name' => 'Ventanilla 1']);

        $first = Turn::factory()->create([
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

        $this->actingAs($agent)
            ->post(route('agent.call'), [
                'service_id' => $service->id,
                'counter_id' => $counter->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $first->refresh();
        $second->refresh();

        $this->assertSame(TurnStatus::Called, $first->status);
        $this->assertSame($counter->id, $first->counter_id);
        $this->assertSame($agent->id, $first->user_id);
        $this->assertNotNull($first->called_at);

        $this->assertSame(TurnStatus::Waiting, $second->status);

        Event::assertDispatched(
            TurnCalled::class,
            fn (TurnCalled $event) => $event->turn->is($first),
        );
    }

    public function test_agent_cannot_call_another_turn_while_one_is_active(): void
    {
        $agent = User::factory()->create();
        $service = Service::factory()->create();
        $counter = Counter::factory()->create();

        Turn::factory()->create([
            'service_id' => $service->id,
            'status' => TurnStatus::Called,
            'counter_id' => $counter->id,
            'user_id' => $agent->id,
            'called_at' => now(),
        ]);

        $this->actingAs($agent)
            ->post(route('agent.call'), [
                'service_id' => $service->id,
                'counter_id' => $counter->id,
            ])
            ->assertSessionHasErrors('service_id');

        $this->assertSame(1, Turn::query()->where('status', TurnStatus::Called)->count());
    }

    public function test_agent_can_call_the_active_turn_again(): void
    {
        Event::fake([TurnCalled::class]);

        $agent = User::factory()->create();
        $service = Service::factory()->create();
        $counter = Counter::factory()->create(['name' => 'Ventanilla 2']);

        $turn = Turn::factory()->create([
            'service_id' => $service->id,
            'status' => TurnStatus::Called,
            'counter_id' => $counter->id,
            'user_id' => $agent->id,
            'called_at' => now()->subMinutes(2),
            'number' => 1,
            'code' => 'A-001',
        ]);

        $this->actingAs($agent)
            ->post(route('agent.recall', $turn))
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertSame(TurnStatus::Called, $turn->refresh()->status);

        Event::assertDispatched(
            TurnCalled::class,
            fn (TurnCalled $event) => $event->turn->is($turn),
        );
    }

    public function test_agent_cannot_call_again_a_turn_attended_by_another_agent(): void
    {
        Event::fake([TurnCalled::class]);

        $agent = User::factory()->create();
        $service = Service::factory()->create();

        $turn = Turn::factory()->create([
            'service_id' => $service->id,
            'status' => TurnStatus::Called,
            'user_id' => User::factory()->create()->id,
            'called_at' => now(),
        ]);

        $this->actingAs($agent)
            ->post(route('agent.recall', $turn))
            ->assertRedirect()
            ->assertSessionHas('error');

        Event::assertNotDispatched(TurnCalled::class);
    }

    public function test_agent_finishes_an_active_attention(): void
    {
        $agent = User::factory()->create();
        $service = Service::factory()->create();

        $turn = Turn::factory()->create([
            'service_id' => $service->id,
            'status' => TurnStatus::InService,
            'user_id' => $agent->id,
            'called_at' => now()->subMinutes(4),
            'started_at' => now()->subMinutes(3),
            'number' => 1,
            'code' => 'A-001',
        ]);

        $this->actingAs($agent)
            ->post(route('agent.finish', $turn))
            ->assertRedirect()
            ->assertSessionHas('status');

        $turn->refresh();

        $this->assertSame(TurnStatus::Completed, $turn->status);
        $this->assertNotNull($turn->finished_at);
    }

    public function test_calling_without_waiting_turns_returns_an_error(): void
    {
        $agent = User::factory()->create();
        $service = Service::factory()->create();
        $counter = Counter::factory()->create();

        $this->actingAs($agent)
            ->post(route('agent.call'), [
                'service_id' => $service->id,
                'counter_id' => $counter->id,
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('turns', 0);
    }
}
