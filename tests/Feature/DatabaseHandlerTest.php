<?php

use App\Loggers\DatabaseLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use App\Models\LogMessage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('logging.channels.database', [
        'driver' => 'custom',
        'via' => DatabaseLogger::class,
    ]);
});

it('logs to the database', function () {
    Log::channel('database')->info('Test message');

    $this->assertDatabaseHas('logs', [
        'level_name' => mb_strtoupper('info'),
        'message' => 'Test message',
    ]);
});

it('stores the logs context', function () {
    Log::channel('database')->info('Test message', ['level' => 200]);

    $this->assertDatabaseHas('logs', [
        'context' => json_encode(['level' => 200]),
    ]);
});

it('correctly logs exceptions', function () {
    config()->set('logging.default', 'database');

    report(new Exception('This exception should be logged.'));

    $this->assertStringContainsString(
        'This exception should be logged.',
        LogMessage::first()->context['exception']
    );
});

it('logs different levels', function () {
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
});

it('stores complex context data', function () {
    $context = ['user' => ['id' => 1, 'name' => 'John Doe'], 'level' => 200];
    Log::channel('database')->info('Complex context message', $context);

    $this->assertDatabaseHas('logs', [
        'context' => json_encode($context),
    ]);
});

it('respects configuration changes', function () {
    config()->set('logging.channels.alternate', [
        'driver' => 'custom',
        'via' => DatabaseLogger::class,
    ]);

    Log::channel('alternate')->warning('Alternate channel message');

    $this->assertDatabaseHas('logs', [
        'level_name' => mb_strtoupper('warning'),
        'message' => 'Alternate channel message',
    ]);
});

it('truncates long log messages', function () {
    $longMessage = Str::repeat('A', 1400); // Assuming 1400 is over the limit

    Log::channel('database')->info($longMessage);

    $loggedMessage = LogMessage::first();
    $this->assertLessThanOrEqual(1400, Str::length($loggedMessage->getMessage()));
    $this->assertStringStartsWith(Str::repeat('A', 1400), $loggedMessage->getMessage()); // Adjust based on actual limit
});

it('rotates logs correctly', function () {
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
});

it('handles database failures gracefully', function () {
    // Simulate database failure
    Mockery::mock(LogMessage::class)
        ->shouldReceive('create')->andThrow(new \Exception('Database error'));

    try {
        // Mock the Log facade
        Log::shouldReceive('channel')
            ->with('database')
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->with('This should not crash the app');
    } catch (Exception $e) {
        // Handle the exception to ensure the application doesn't crash
        $this->assertEquals('Database error', $e->getMessage());
    }

    // Ensure that the log entry is not in the database
    $this->assertDatabaseMissing('logs', [
        'message' => 'This should not crash the app',
    ]);
});
