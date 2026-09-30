<?php

use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    Date::setTestNow('2026-09-30 14:15:16');
});

it('creates a timestamped data migration in the data migrations folder', function () {
    $this->artisan('make:data-migration', ['name' => 'fill_countries'])->assertSuccessful();

    $filePath = database_path('data-migrations/2026_09_30_141516_fill_countries.php');

    expect($filePath)->toBeFile()
        ->and(File::get($filePath))->toBe(File::get(__DIR__.'/../../stubs/data-migration.stub'));
});

it('creates the data migration in the configured folder', function () {
    config()->set('data-migrations.folder', 'custom-data');

    $this->artisan('make:data-migration', ['name' => 'fill_countries'])->assertSuccessful();

    expect(database_path('custom-data/2026_09_30_141516_fill_countries.php'))->toBeFile()
        ->and(database_path('data-migrations'))->not->toBeDirectory();
});

it('converts the name to snake case', function () {
    $this->artisan('make:data-migration', ['name' => 'FillCountries'])->assertSuccessful();

    expect(database_path('data-migrations/2026_09_30_141516_fill_countries.php'))->toBeFile();
});

it('refuses to overwrite an existing data migration', function () {
    $filePath = database_path('data-migrations/2026_09_30_141516_fill_countries.php');
    File::ensureDirectoryExists(dirname($filePath));
    File::put($filePath, 'existing');

    $this->artisan('make:data-migration', ['name' => 'fill_countries'])->assertFailed();

    expect(File::get($filePath))->toBe('existing');
});

it('uses the published stub when it exists', function () {
    File::ensureDirectoryExists(base_path('stubs'));
    File::put(base_path('stubs/data-migration.stub'), 'published stub');

    $this->artisan('make:data-migration', ['name' => 'fill_countries'])->assertSuccessful();

    expect(File::get(database_path('data-migrations/2026_09_30_141516_fill_countries.php')))->toBe('published stub');
});

it('prefers the configured stub over the published stub', function () {
    File::ensureDirectoryExists(base_path('stubs'));
    File::put(base_path('stubs/data-migration.stub'), 'published stub');
    File::put(base_path('stubs/configured.stub'), 'configured stub');
    config()->set('data-migrations.stub', base_path('stubs/configured.stub'));

    $this->artisan('make:data-migration', ['name' => 'fill_countries'])->assertSuccessful();

    expect(File::get(database_path('data-migrations/2026_09_30_141516_fill_countries.php')))->toBe('configured stub');
});
