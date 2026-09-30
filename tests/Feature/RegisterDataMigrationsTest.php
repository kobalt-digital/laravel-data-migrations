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

it('registers the data migrations folder with the migrator', function () {
    expect(app('migrator')->paths())->toContain(database_path('data-migrations'));
});

it('registers the configured folder with the migrator', function () {
    config()->set('data-migrations.folder', 'custom-data');

    (new DataMigrationsServiceProvider(app()))->boot();

    expect(app('migrator')->paths())->toContain(database_path('custom-data'));
});

it('leaves the migrator alone when running with migrate is disabled', function () {
    config()->set('data-migrations.folder', 'custom-data');
    config()->set('data-migrations.run_with_migrate', false);

    (new DataMigrationsServiceProvider(app()))->boot();

    expect(app('migrator')->paths())->not->toContain(database_path('custom-data'));
});

it('runs and rolls back data migrations after the schema migrations they depend on', function () {
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

    $this->artisan('migrate')->assertSuccessful();

    expect(DB::table('countries')->pluck('code')->all())->toBe(['NL'])
        ->and(DB::table('migrations')->pluck('migration')->all())->toContain('2026_01_02_000000_fill_countries');

    $this->artisan('migrate:rollback', ['--step' => 1])->assertSuccessful();

    expect(DB::table('countries')->count())->toBe(0)
        ->and(DB::table('migrations')->pluck('migration')->all())->not->toContain('2026_01_02_000000_fill_countries');
});
