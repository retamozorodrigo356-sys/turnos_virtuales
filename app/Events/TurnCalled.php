<?php

namespace App\Events;

use App\Models\Turn;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TurnCalled implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Turn $turn)
    {
        $this->turn->loadMissing(['service', 'counter']);
    }

    public function broadcastOn(): Channel
    {
        return new Channel('queue');
    }

    public function broadcastAs(): string
    {
        return 'turn.called';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->turn->id,
            'code' => $this->turn->code,
            'service' => $this->turn->service->name,
            'prefix' => $this->turn->service->prefix,
            'counter' => $this->turn->counter?->name,
            'called_at' => $this->turn->called_at?->toIso8601String(),
        ];
    }
}
