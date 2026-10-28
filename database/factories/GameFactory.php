<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class GameFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->words(3, true),
            'developer' => fake()->company(),
            'released_on' => fake()->date(),
            'description' => fake()->paragraph(),
        ];
    }
}
