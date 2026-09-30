<?php

namespace Kobalt\DataMigrations\Tests;

use Illuminate\Filesystem\Filesystem;
use Kobalt\DataMigrations\DataMigrationsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function tearDown(): void
    {
        $files = new Filesystem;

        $files->deleteDirectory(database_path('data-migrations'));
        $files->deleteDirectory(database_path('custom-data'));
        $files->deleteDirectory(base_path('stubs'));

        parent::tearDown();
    }

    protected function getPackageProviders($app): array
    {
        return [
            DataMigrationsServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
    }
}
