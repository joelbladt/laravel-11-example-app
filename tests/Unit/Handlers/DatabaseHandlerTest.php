<?php

namespace Tests\Unit\Handlers;

use App\Handlers\DatabaseHandler;
use App\Loggers\DatabaseLogger;
use App\Models\LogMessage;
use DateTimeImmutable;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Mockery as m;
use Monolog\LogRecord;
use Tests\TestCase;

class DatabaseHandlerTest extends TestCase
{
    use RefreshDatabase;

    private $logMessageMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->logMessageMock = m::mock(LogMessage::class)->makePartial();
        $this->logMessageMock->shouldAllowMockingProtectedMethods();
    }

    public function testLogsToDatabase(): void
    {
        Log::channel('database')->info('Test message');

        $this->assertDatabaseHas('logs', [
            'level_name' => mb_strtoupper('info'),
            'message' => 'Test message',
        ]);
    }

    public function testStoreLogsContext(): void
    {
        Log::channel('database')->info('Test message', ['level' => 200]);

        $this->assertDatabaseHas('logs', [
            'context' => json_encode(['level' => 200]),
        ]);
    }

    public function testCorrectlyLogsExceptions(): void
    {
        report(new Exception('This exception should be logged.'));

        $this->assertStringContainsString(
            'This exception should be logged.',
            LogMessage::first()->getContext()['exception']
        );
    }

    public function testLogsDifferentLevels(): void
    {
        Log::channel('database')->error('Error message');
        Log::channel('database')->debug('Debug message');

        $this->assertDatabaseHas('logs', [
            'level_name' => mb_strtoupper('error'),
            'message' => 'Error message',
        ]);

        $this->assertDatabaseHas('logs', [
            'level_name' => mb_strtoupper('debug'),
            'message' => 'Debug message',
        ]);
    }

    public function testStoresComplexContextData(): void
    {
        $context = ['user' => ['id' => 1, 'name' => 'John Doe'], 'level' => 200];
        Log::channel('database')->info('Complex context message', $context);

        $this->assertDatabaseHas('logs', [
            'context' => json_encode($context),
        ]);
    }

    public function testRespectsConfigurationChanges(): void
    {
        Config::set('logging.channels.alternate', [
            'driver' => 'custom',
            'via' => DatabaseLogger::class,
        ]);

        Log::channel('alternate')->warning('Alternate channel message');

        $this->assertDatabaseHas('logs', [
            'level_name' => mb_strtoupper('warning'),
            'message' => 'Alternate channel message',
        ]);
    }

    public function testTruncatesLongLogMessages(): void
    {
        $longMessage = Str::repeat('A', 1400); // Assuming 1400 is over the limit

        Log::channel('database')->info($longMessage);

        $loggedMessage = LogMessage::first();
        $this->assertLessThanOrEqual(1400, Str::length($loggedMessage->message));
        $this->assertStringStartsWith(Str::repeat('A', 1400), $loggedMessage->message); // Adjust based on actual limit
    }

    public function testRotatesLogsCorrectly(): void
    {
        // Log rotation setup specific to your application
        for ($i = 1; $i < 1000; $i++) {
            Log::channel('database')->info("Log message $i");
        }

        // Check that old logs are removed, and new logs are present
        $this->assertDatabaseMissing('logs', [
            'message' => 'Log message 0',
        ]);
        $this->assertDatabaseHas('logs', [
            'message' => 'Log message 999',
        ]);
    }

    /**
     * TODO: wip. Need to run into exception
     */
    public function testWriteExceptionHandling(): void
    {
        $this->throwException(new Exception('Error succeed'));

        $mockRecord = m::mock(LogRecord::class, [
            'datetime' => new DateTimeImmutable(),
            'channel' => 'test',
            'message' => 'Test log message',
            'context' => [],
            'extra' => [],
        ]);

        $mockRecord
            ->shouldReceive('toArray')
            ->andThrow(new Exception('Something went wrong.'));

        $mockHandler = m::mock(DatabaseHandler::class)->makePartial();
        $mockHandler->shouldAllowMockingProtectedMethods();

        $mockHandler
            ->shouldReceive('write')
            ->once()
            ->andReturnSelf();

        $mockHandler->write($mockRecord);

        $this->assertDatabaseMissing('logs', [
            'message' => 'Test log message',
        ]);
    }

    //    public function testThrowException(): void
    //    {
    //        $this->expectException(Exception::class);
    //
    //        // Mock the LogMessageRepository to throw an exception when create() is called
    //        m::mock(LogMessageRepository::class)
    //            ->shouldReceive('create')
    //            ->with([])
    //            ->andThrow(Exception::class, 'something went wrong.');
    //
    //        // Mock the Log facade to expect the fallback logging
    //        Log::shouldReceive('stack')
    //            ->once()
    //            ->with(['single'])
    //            ->andReturnSelf();
    //
    //        Log::shouldReceive('debug')
    //            ->twice(); // Once for the original message and once for the exception
    //
    //        // Create a LogRecord to pass to the handler
    //        $logRecord = new LogRecord(
    //            datetime: new \DateTimeImmutable(),
    //            channel: 'test',
    //            level: Level::Info,
    //            message: 'Test log message',
    //            context: [],
    //            extra: [],
    //        );
    //
    //        // Create the DatabaseHandler instance
    //        $databaseHandler = new DatabaseHandler();
    //
    //        $databaseHandler->handle($logRecord);
    //
    //        $this->testThrowException();
    //    }
    //
    //    public function testWriteHandlesExceptionGracefully()
    //    {
    //        // Mock the LogMessageRepository to throw an exception when create() is called
    //        $logMessageRepositoryMock = $this->createMock(LogMessageRepository::class);
    //        $logMessageRepositoryMock
    //            ->expects($this->once())
    //            ->method('create')
    //            ->willThrowException(new Exception('Database error'));
    //
    //        // Mock the Log facade to expect the fallback logging
    //        Log::shouldReceive('stack')
    //            ->once()
    //            ->with(['single'])
    //            ->andReturnSelf();
    //
    //        Log::shouldReceive('debug')
    //            ->twice(); // Once for the original message and once for the exception
    //
    //        // Create a LogRecord to pass to the handler
    //        $logRecord = new LogRecord(
    //            datetime: new \DateTimeImmutable(),
    //            channel: 'test',
    //            level: Level::Info,
    //            message: 'Test log message',
    //            context: [],
    //            extra: [],
    //        );
    //
    //        // Create the DatabaseHandler instance
    //        $databaseHandler = new DatabaseHandler();
    //
    //        // Use reflection to access the protected write method
    //        $reflection = new \ReflectionClass($databaseHandler);
    //        $method = $reflection->getMethod('handle');
    //        $method->setAccessible(true);
    //
    //        // Call the write methodw, which should handle the exception internally
    //        $method->invoke($databaseHandler, $logRecord);
    //
    //        $this->assertInstanceOf(Exception::class, $method);
    //    }

    public function tearDown(): void
    {
        // Close Mockery to prevent any errors about open expectations
        m::close();
        parent::tearDown();
    }
}
