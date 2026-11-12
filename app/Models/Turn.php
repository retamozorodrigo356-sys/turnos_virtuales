<?php

namespace App\Models;

use App\TurnStatus;
use Database\Factories\TurnFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'public_id',
    'service_id',
    'counter_id',
    'user_id',
    'number',
    'code',
    'status',
    'issued_on',
    'called_at',
    'started_at',
    'finished_at',
])]
class Turn extends Model
{
    /** @use HasFactory<TurnFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TurnStatus::class,
            'issued_on' => 'date',
            'called_at' => 'datetime',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Turn $turn): void {
            $turn->public_id ??= (string) Str::uuid();
            $turn->issued_on ??= today()->toDateString();
        });
    }

    /**
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * @return BelongsTo<Counter, $this>
     */
    public function counter(): BelongsTo
    {
        return $this->belongsTo(Counter::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    /**
     * @param  Builder<Turn>  $query
     */
    public function scopeIssuedToday(Builder $query): void
    {
        $query->whereDate('issued_on', today()->toDateString());
    }

    /**
     * @param  Builder<Turn>  $query
     */
    public function scopeWaiting(Builder $query): void
    {
        $query->where('status', TurnStatus::Waiting);
    }

    /**
     * Citizens still ahead in the same queue today.
     */
    public function waitingAhead(): int
    {
        if ($this->status !== TurnStatus::Waiting) {
            return 0;
        }

        return static::query()
            ->where('service_id', $this->service_id)
            ->whereDate('issued_on', $this->issued_on->toDateString())
            ->waiting()
            ->where('id', '<', $this->id)
            ->count();
    }

    public function estimatedWaitMinutes(): int
    {
        return $this->waitingAhead() * $this->service->average_minutes;
    }
}
