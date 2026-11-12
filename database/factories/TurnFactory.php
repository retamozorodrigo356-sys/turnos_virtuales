<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\Turn;
use App\TurnStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Turn>
 */
class TurnFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_id' => Service::factory(),
            'number' => fake()->numberBetween(1, 999),
            'code' => strtoupper(fake()->unique()->bothify('?##-###')),
            'status' => TurnStatus::Waiting,
            'issued_on' => today()->toDateString(),
        ];
    }

    public function called(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TurnStatus::Called,
            'called_at' => now(),
        ]);
    }

    public function inService(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TurnStatus::InService,
            'called_at' => now(),
            'started_at' => now(),
        ]);
    }
}
