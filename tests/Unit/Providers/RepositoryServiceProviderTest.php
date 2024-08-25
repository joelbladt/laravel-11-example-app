<?php

namespace Tests\Unit\Providers;

use App\Interfaces\Eloquent\LogMessageInterface;
use Tests\TestCase;

class RepositoryServiceProviderTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testItSetupTheLogMessageRepository(): void
    {
        $repo = $this->app->make('App\Repositories\Eloquent\LogMessageRepository');

        $this->assertInstanceOf(LogMessageInterface::class, $repo);
    }
}
