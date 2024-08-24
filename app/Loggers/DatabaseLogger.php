<?php

namespace App\Loggers;

use App\Handlers\DatabaseHandler;
use Monolog\Logger;

class DatabaseLogger
{
    /**
     * Create a custom Monolog instance.
     *
     * @return Logger
     */
    public function __invoke(array $config): Logger
    {
        return new Logger('database', [
            new DatabaseHandler(),
        ]);
    }
}
