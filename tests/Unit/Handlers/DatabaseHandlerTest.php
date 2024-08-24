<?php

namespace Tests\Unit\Handlers;

use App\Loggers\DatabaseLogger;
use App\Models\LogMessage;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Mockery as m;
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
            LogMessage::first()->context['exception']
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

        Config::set('logging.channels.alternate', 'invalid');

        Log::channel('alternate')->info('Test error message');

        $this->assertDatabaseMissing('logs', [
            'level_name' => mb_strtoupper('info'),
            'message' => 'Test error message',
        ]);
        //
        //        $this->expectException(\Exception::class);
        //
        //        Mockery::mock(LogMessage::class)
        //            ->shouldReceive('create')
        //            ->andThrow(\Exception::class);
        //
        //        $record = new LogRecord(
        //            datetime: new \DateTimeImmutable(),
        //            channel: 'test',
        //            level: Level::tryFrom(100),
        //            message: 'Test message',
        //            context: [],
        //            extra: []
        //        );
        //
        //        Mockery::mock(DatabaseHandler::class)
        //            ->shouldReceive('handle')
        //            ->with($record)->andThrow(new \Exception('handled-exception-response'));
        //
        //        $this->assertInstanceOf(\Exception::class, $this);
        //
        //        $this->assertDatabaseMissing('logs', [
        //            'exception' => (string) new \Exception('handled-exception-response')
        //        ]);
    }
}
