<?php

use App\Loggers\DatabaseLogger;
use App\Models\LogMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('logging.channels.database', [
        'driver' => 'custom',
        'via' => DatabaseLogger::class,
    ]);

    Log::channel('database')->info('Test info message.');
    Log::channel('database')->error('Test error message.');
});

test('get log message from database', function () {
    $logMessage = LogMessage::first();

    $this->assertSame('Test info message.', $logMessage->getMessage());
});

test('get log level from database', function () {
    $logMessage = LogMessage::first();

    $this->assertSame(200, $logMessage->getLevel());
});

test('get log level name from database', function () {
    $logMessage = LogMessage::first();

    $this->assertSame('INFO', $logMessage->getStatus());
});

test('get logged time from database', function () {
    $logMessage = LogMessage::first();

    $this->assertSame(now()->toDateTimeString(), $logMessage->getLogged());
});

test('get logged context from database', function () {

    $logMessage = LogMessage::latest()->first();

    $this->assertSame([], $logMessage->getContext()->toArray());
});

test('get logged extra context from database', function () {

    $logMessage = LogMessage::latest()->first();

    $this->assertSame([], $logMessage->getExtra()->toArray());
});
