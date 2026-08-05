<?php

namespace Database\Factories;

use App\Enums\BoardCategory;
use App\Enums\BoardVisibility;
use App\Models\Board;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Board>
 */
class BoardFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'category' => fake()->randomElement(BoardCategory::cases()),
            'canvas_data' => ['elements' => []],
            'visibility' => BoardVisibility::Private,
        ];
    }

    public function public(): static
    {
        return $this->state(fn () => ['visibility' => BoardVisibility::Public]);
    }

    public function category(BoardCategory $category): static
    {
        return $this->state(fn () => ['category' => $category]);
    }
}
