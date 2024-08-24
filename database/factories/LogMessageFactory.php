<?php

namespace Database\Factories;

use App\Models\LogMessage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Monolog\Level;
use Psr\Log\LogLevel;

class LogMessageFactory extends Factory
{
    protected $model = LogMessage::class;

    public function definition(): array
    {
        $randomLevelName = fake()->randomElement([
            LogLevel::DEBUG, LogLevel::INFO, LogLevel::ERROR, LogLevel::ALERT,
            LogLevel::NOTICE, LogLevel::WARNING, LogLevel::CRITICAL, LogLevel::EMERGENCY,
        ]);

        return [
            'level_name' => $randomLevelName,
            'level' => Level::fromName($randomLevelName),
            'message' => fake()->sentence(),
            'logged_at' => now(),
            'context' => [],
            'extra' => [],
        ];
    }
}
