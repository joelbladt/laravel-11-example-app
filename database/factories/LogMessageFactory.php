<?php

namespace Database\Factories;

use App\Models\LogMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

class LogMessageFactory extends Factory
{
    protected $model = LogMessage::class;

    public function definition(): array
    {
        return [
            'level_name' => fake()->randomElement(['error', 'warning', 'info']),
            'level' => fake()->randomElement(['200', '500', '503']),
            'message' => fake()->sentence(),
            'logged_at' => now(),
        ];
    }
}
