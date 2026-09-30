<?php

namespace Kobalt\DataMigrations;

use Illuminate\Support\ServiceProvider;
use Kobalt\DataMigrations\Commands\MakeDataMigrationCommand;

class DataMigrationsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $publishedConfig = $this->app['config']->get('data-migrations');

        $this->mergeConfigFrom(__DIR__.'/../config/data-migrations.php', 'data-migrations');

        if (is_array($publishedConfig) && ! array_key_exists('run_with_migrate', $publishedConfig)) {
            $this->app['config']->set('data-migrations.run_with_migrate', false);
        }
    }

    public function boot(): void
    {
        if (config('data-migrations.run_with_migrate', false)) {
            $this->loadMigrationsFrom(DataMigrations::path());
        }

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            MakeDataMigrationCommand::class,
        ]);

        $this->publishes([
            __DIR__.'/../config/data-migrations.php' => config_path('data-migrations.php'),
        ], 'data-migrations-config');

        $this->publishes([
            __DIR__.'/../stubs/data-migration.stub' => base_path('stubs/data-migration.stub'),
        ], 'data-migrations-stubs');
    }
}
