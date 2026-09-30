<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Kobalt\DataMigrations\DataMigrations;
use Kobalt\DataMigrations\DataMigrationsServiceProvider;

it('stores data migrations in database/data-migrations by default', function () {
    expect(DataMigrations::path())->toBe(database_path('data-migrations'));
});

it('uses the configured folder name', function () {
    config()->set('data-migrations.folder', 'custom-data');

    expect(DataMigrations::path())->toBe(database_path('custom-data'));
});

it('prefers the configured absolute path over the folder name', function () {
    config()->set('data-migrations.folder', 'custom-data');
    config()->set('data-migrations.path', '/tmp/elsewhere/');

    expect(DataMigrations::path())->toBe('/tmp/elsewhere');
});

/**
 * Register and boot the provider as if the given config file had been published.
 *
 * @param  array<string, mixed>  $publishedConfig
 */
function bootWithPublishedConfig(array $publishedConfig): void
{
    config()->set('data-migrations', $publishedConfig);

    $provider = new DataMigrationsServiceProvider(app());
    $provider->register();
    $provider->boot();
}

it('registers the data migrations folder with the migrator by default', function () {
    expect(config('data-migrations.run_with_migrate'))->toBeTrue()
        ->and(app('migrator')->paths())->toContain(database_path('data-migrations'));
});

it('registers the configured folder with the migrator', function () {
    bootWithPublishedConfig(['folder' => 'custom-data', 'run_with_migrate' => true]);

    expect(app('migrator')->paths())->toContain(database_path('custom-data'));
});

it('leaves the migrator alone when running with migrate is disabled', function () {
    bootWithPublishedConfig(['folder' => 'custom-data', 'run_with_migrate' => false]);

    expect(app('migrator')->paths())->not->toContain(database_path('custom-data'));
});

it('leaves the migrator alone when a published config omits running with migrate', function () {
    bootWithPublishedConfig(['folder' => 'custom-data']);

    expect(config('data-migrations.run_with_migrate'))->toBeFalse()
        ->and(app('migrator')->paths())->not->toContain(database_path('custom-data'));
});

it('fills other keys omitted from a published config with the package defaults', function () {
    bootWithPublishedConfig(['run_with_migrate' => true]);

    expect(config('data-migrations.folder'))->toBe('data-migrations')
        ->and(config('data-migrations.stub'))->toBeNull();
});

/**
 * Write a schema migration that creates a table and a data migration that fills it.
 */
function writeCountriesMigrations(): void
{
    $schemaPath = database_path('custom-data');
    File::ensureDirectoryExists($schemaPath);
    File::ensureDirectoryExists(DataMigrations::path());
    app('migrator')->path($schemaPath);

    File::put($schemaPath.'/2026_01_01_000000_create_countries_table.php', <<<'PHP'
            <?php

            use Illuminate\Database\Migrations\Migration;
            use Illuminate\Database\Schema\Blueprint;
            use Illuminate\Support\Facades\Schema;

            return new class extends Migration
            {
                public function up(): void
                {
                    Schema::create('countries', function (Blueprint $table) {
                        $table->id();
                        $table->string('code');
                    });
                }

                public function down(): void
                {
                    Schema::drop('countries');
                }
            };
            PHP);

    File::put(DataMigrations::path().'/2026_01_02_000000_fill_countries.php', <<<'PHP'
            <?php

            use Illuminate\Database\Migrations\Migration;
            use Illuminate\Support\Facades\DB;

            return new class extends Migration
            {
                public function up(): void
                {
                    DB::table('countries')->insert(['code' => 'NL']);
                }

                public function down(): void
                {
                    DB::table('countries')->where('code', 'NL')->delete();
                }
            };
            PHP);
}

it('does not run data migrations with migrate when a published config omits running with migrate', function () {
    bootWithPublishedConfig(['folder' => 'guarded-data']);
    writeCountriesMigrations();

    $this->artisan('migrate')->assertSuccessful();

    expect(DB::table('countries')->count())->toBe(0)
        ->and(DB::table('migrations')->pluck('migration')->all())->not->toContain('2026_01_02_000000_fill_countries');
});

it('runs and rolls back data migrations after the schema migrations they depend on', function () {
    writeCountriesMigrations();

    $this->artisan('migrate')->assertSuccessful();

    expect(DB::table('countries')->pluck('code')->all())->toBe(['NL'])
        ->and(DB::table('migrations')->pluck('migration')->all())->toContain('2026_01_02_000000_fill_countries');

    $this->artisan('migrate:rollback', ['--step' => 1])->assertSuccessful();

    expect(DB::table('countries')->count())->toBe(0)
        ->and(DB::table('migrations')->pluck('migration')->all())->not->toContain('2026_01_02_000000_fill_countries');
});
