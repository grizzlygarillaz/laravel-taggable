<?php

namespace Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use function Orchestra\Testbench\workbench_path;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // Load package migrations
        $this->loadMigrationsFrom(workbench_path('database/migrations'));

        // Or if using built-in Laravel migrations
        // $this->artisan('migrate', ['--database' => 'testing'])->run();
    }
}
