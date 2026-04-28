<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiaryEntryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'recorded_at' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'mood'        => $this->faker->numberBetween(1, 5),
            'energy'      => $this->faker->numberBetween(1, 10),
            'sleep_hours' => $this->faker->randomFloat(1, 4, 10),
            'notes'       => $this->faker->optional()->sentence(),
        ];
    }
}
