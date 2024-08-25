<?php

namespace App\Providers;

use App\Interfaces\Eloquent\LogMessageInterface;
use App\Repositories\Eloquent\LogMessageRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(LogMessageInterface::class, LogMessageRepository::class);
    }
}
