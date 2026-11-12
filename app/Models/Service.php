<?php

namespace App\Models;

use App\TurnStatus;
use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'prefix', 'description', 'average_minutes', 'is_active'])]
class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'average_minutes' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Turn, $this>
     */
    public function turns(): HasMany
    {
        return $this->hasMany(Turn::class);
    }

    /**
     * @param  Builder<Service>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Active services with the number of citizens still waiting today.
     *
     * @return Collection<int, Service>
     */
    public static function withWaitingCounts(): Collection
    {
        return static::query()
            ->active()
            ->withCount([
                'turns as waiting_count' => fn (Builder $query) => $query
                    ->whereDate('issued_on', today()->toDateString())
                    ->where('status', TurnStatus::Waiting),
            ])
            ->orderBy('name')
            ->get();
    }
}
