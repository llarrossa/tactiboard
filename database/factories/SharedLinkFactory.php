<?php

namespace Database\Factories;

use App\Models\Board;
use App\Models\SharedLink;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SharedLink>
 */
class SharedLinkFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'board_id' => Board::factory(),
            'token' => Str::random(32),
            'expires_at' => null,
        ];
    }

    public function expired(): static
    {
        return $this->state(fn () => ['expires_at' => now()->subDay()]);
    }

    public function expiringAt(\DateTimeInterface $moment): static
    {
        return $this->state(fn () => ['expires_at' => $moment]);
    }
}
