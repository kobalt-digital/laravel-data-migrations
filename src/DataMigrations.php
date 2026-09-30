<?php

namespace Kobalt\DataMigrations;

class DataMigrations
{
    /**
     * Resolve the directory that holds the data migrations.
     */
    public static function path(): string
    {
        $path = config('data-migrations.path');

        if (filled($path)) {
            return rtrim($path, '/\\');
        }

        return database_path(trim(config('data-migrations.folder', 'data-migrations'), '/\\'));
    }

    /**
     * Resolve the stub used to generate a new data migration.
     */
    public static function stubPath(): string
    {
        $configuredStub = config('data-migrations.stub');

        if (filled($configuredStub)) {
            return $configuredStub;
        }

        $publishedStub = base_path('stubs/data-migration.stub');

        if (file_exists($publishedStub)) {
            return $publishedStub;
        }

        return __DIR__.'/../stubs/data-migration.stub';
    }
}
