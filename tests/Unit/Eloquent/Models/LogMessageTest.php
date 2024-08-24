<?php

namespace Tests\Unit\Eloquent\Models;

use App\Models\LogMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Monolog\Level;
use Psr\Log\LogLevel;
use Tests\TestCase;

class LogMessageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        LogMessage::factory()->create([
            'level_name' => LogLevel::INFO,
            'level' => Level::Info,
            'message' => 'Test info message.',
        ]);

        LogMessage::factory()->create([
            'level_name' => LogLevel::ERROR,
            'level' => Level::Error,
            'message' => 'Test error message.',
        ]);
    }

    public function testGetMessageFromDatabase(): void
    {
        $logMessage = LogMessage::first();

        $this->assertSame('Test info message.', $logMessage->message);
    }

    public function testGetLogLevelFromDatabase(): void
    {
        $logMessage = LogMessage::first();

        $this->assertSame(200, $logMessage->level);
    }

    public function testGetLogLevelNameFromDatabase(): void
    {
        $logMessage = LogMessage::first();

        $this->assertSame('INFO', $logMessage->level_name);
    }

    public function testGetLoggedTimeFromDatabase(): void
    {
        $logMessage = LogMessage::first();

        $this->assertSame(now()->toDateTimeString(), $logMessage->logged_at);
    }

    public function testGetLoggedContextFromDatabase(): void
    {
        $logMessage = LogMessage::latest()->first();

        $this->assertSame([], $logMessage->getContext()->toArray());
    }

    public function testGetLoggedExtraContextFromDatabase(): void
    {
        $logMessage = LogMessage::latest()->first();

        $this->assertSame([], $logMessage->getExtra()->toArray());
    }
}
