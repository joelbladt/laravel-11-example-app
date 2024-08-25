<?php

namespace Tests\Unit\Eloquent\Models;

use App\Interfaces\Eloquent\LogMessageInterface;
use App\Repositories\Eloquent\LogMessageRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Monolog\Level;
use Psr\Log\LogLevel;
use Tests\TestCase;

class LogMessageTest extends TestCase
{
    use RefreshDatabase;

    private LogMessageInterface $logger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = new LogMessageRepository();

        $this->logger->create([
            'level_name' => LogLevel::INFO,
            'level' => Level::Info,
            'message' => 'Test info message.',
        ]);

        $this->logger->create([
            'level_name' => LogLevel::ERROR,
            'level' => Level::Error,
            'message' => 'Test error message.',
        ]);
    }

    public function testGetMessageFromDatabase(): void
    {
        $logMessage = $this->logger->all();

        $result = $logMessage->first();

        $this->assertSame('Test info message.', $result->message);
    }

    public function testGetMessageFromDatabaseById(): void
    {
        $logMessage = $this->logger->find(2);

        $result = $logMessage->getOriginal('message');

        $this->assertSame('Test error message.', $result);
    }

    public function testGetLogLevelFromDatabase(): void
    {
        $logMessage = $this->logger->all();

        $result = $logMessage->first();

        $this->assertSame(200, $result->level);
    }

    public function testGetLogLevelNameFromDatabase(): void
    {
        $logMessage = $this->logger->all();

        $result = $logMessage->first();

        $this->assertSame('INFO', $result->level_name);
    }

    public function testGetLoggedTimeFromDatabase(): void
    {
        $logMessage = $this->logger->all();

        $result = $logMessage->first();

        $this->assertSame(now()->toDateTimeString(), $result->logged_at);
    }

    public function testGetLoggedContextFromDatabase(): void
    {
        $logMessage = $this->logger->all();

        $result = $logMessage->last();

        $this->assertSame([], $result->getContext()->toArray());
    }

    public function testGetLoggedExtraContextFromDatabase(): void
    {
        $logMessage = $this->logger->all();

        $result = $logMessage->first();

        $this->assertSame([], $result->getExtra()->toArray());
    }
}
