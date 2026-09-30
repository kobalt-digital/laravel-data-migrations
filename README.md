# Laravel Data Migrations

Keep data migrations (filling, fixing or purging rows) apart from schema migrations (creating and altering tables).

Data migrations live in their own folder, `database/data-migrations` by default, and run with the regular `php artisan migrate`. They share the `migrations` table with schema migrations and run in timestamp order, so a data migration always runs after the schema migrations it depends on. `migrate:status`, `migrate:rollback` and friends work as usual.

## Installation

The package is not on Packagist, so add the repository to your project's `composer.json` first:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "git@github.com:kobalt-digital/laravel-data-migrations.git"
    }
]
```

Then require it:

```bash
composer require kobaltdigital/laravel-data-migrations
```

The service provider is auto-discovered.

## Usage

Create a data migration:

```bash
php artisan make:data-migration fill_zipcode_formats
```

This creates `database/data-migrations/2026_09_30_141516_fill_zipcode_formats.php`. Fill in `up()` and `down()` like any other migration and run `php artisan migrate`.

## Configuration

Publish the config file to change the defaults:

```bash
php artisan vendor:publish --tag=data-migrations-config
```

| Key | Default | Description |
| --- | --- | --- |
| `folder` | `data-migrations` | Folder inside `database/` that holds the data migrations. Also settable with `DATA_MIGRATIONS_FOLDER`. |
| `path` | `null` | Absolute path to store data migrations outside `database/`. Takes precedence over `folder`. |
| `run_with_migrate` | `true` | Register the folder with Laravel's migrator so `php artisan migrate` runs data migrations. Disable it to run them only on demand with `php artisan migrate --path=database/data-migrations`. |
| `stub` | `null` | Absolute path to a custom stub for `make:data-migration`. |

### Custom stub

Publish the stub to `stubs/data-migration.stub` and edit it:

```bash
php artisan vendor:publish --tag=data-migrations-stubs
```

The command uses the stub from the `stub` config key first, then the published stub, then the stub shipped with this package.

## Moving an existing project over

If a project already loads a data migrations folder itself:

1. Require this package.
2. Remove the project's own `loadMigrationsFrom(database_path('data-migrations'))` call and `make:data-migration` command.
3. Point `folder` or `path` at the existing folder if it is not `database/data-migrations`.

Already executed data migrations stay recorded in the `migrations` table, so nothing runs twice. Existing migrations with named classes keep working; new ones are generated as anonymous classes, which avoids class name clashes.

## Testing

```bash
composer test
```

## License

The MIT License (MIT). See [LICENSE.md](LICENSE.md).
