<?php

namespace App\Loggers;

use Monolog\Logger;
use App\Handlers\DatabaseHandler;

class DatabaseLogger
{
    /**
     * Create a custom Monolog instance.
     *
     * @return Logger
     */
    public function __invoke(array $config)
    {
        return new Logger('database', [
            new DatabaseHandler(),
        ]);
    }
}
