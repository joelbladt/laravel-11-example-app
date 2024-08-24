<?php

namespace App\Handlers;

use App\Models\LogMessage;
use Exception;
use Illuminate\Support\Facades\Log;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Throwable;

class DatabaseHandler extends AbstractProcessingHandler
{
    /**
     * {@inheritDoc}
     */
    protected function write(LogRecord $record): void
    {
        if (!is_array($record)) {
            $record = $record->toArray();
        }

        $exception = $record['context']['exception'] ?? null;

        if ($exception instanceof Throwable) {
            $record['context']['exception'] = (string) $exception;
        }

        try {
            LogMessage::create([
                'level' => $record['level'],
                'level_name' => $record['level_name'],
                'message' => $record['message'],
                'logged_at' => $record['datetime'],
                'context' => $record['context'],
                'extra' => $record['extra'],
            ]);
        } catch (Exception $e) {
            $fallbackChannels = config('logging.channels.fallback.channels', ['single']);

            Log::stack($fallbackChannels)->debug($record['formatted'] ?? $record['message']);
            Log::stack($fallbackChannels)->debug('Could not log to the database.', [
                'exception' => $e,
            ]);
        }
    }
}
