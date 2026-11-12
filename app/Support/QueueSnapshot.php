<?php

namespace App\Support;

use App\Models\Service;
use App\Models\Turn;
use App\TurnStatus;
use Illuminate\Database\Eloquent\Collection;

class QueueSnapshot
{
    /**
     * @return array{services: array<int, array{id: int, name: string, prefix: string, waiting: int, estimated_minutes: int}>, waiting_total: int, updated_at: string}
     */
    public static function make(): array
    {
        $services = Service::withWaitingCounts()->map(fn (Service $service) => [
            'id' => $service->id,
            'name' => $service->name,
            'prefix' => $service->prefix,
            'waiting' => (int) $service->waiting_count,
            'estimated_minutes' => (int) $service->waiting_count * $service->average_minutes,
        ]);

        return [
            'services' => $services->values()->all(),
            'waiting_total' => $services->sum('waiting'),
            'updated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * @return Collection<int, Turn>
     */
    public static function calledTurns(): Collection
    {
        return Turn::query()
            ->with(['service', 'counter'])
            ->issuedToday()
            ->whereIn('status', [TurnStatus::Called, TurnStatus::InService])
            ->latest('called_at')
            ->limit(8)
            ->get();
    }
}
