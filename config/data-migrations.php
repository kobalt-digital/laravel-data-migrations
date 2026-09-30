<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Folder name
    |--------------------------------------------------------------------------
    |
    | The folder inside the database directory that holds the data
    | migrations. With the default this is `database/data-migrations`.
    |
    */

    'folder' => env('DATA_MIGRATIONS_FOLDER', 'data-migrations'),

    /*
    |--------------------------------------------------------------------------
    | Absolute path
    |--------------------------------------------------------------------------
    |
    | Set this to store data migrations outside the database directory.
    | When set, it takes precedence over the folder name above.
    |
    */

    'path' => null,

    /*
    |--------------------------------------------------------------------------
    | Run with `php artisan migrate`
    |--------------------------------------------------------------------------
    |
    | When enabled, the data migrations folder is registered with Laravel's
    | migrator, so `migrate`, `migrate:status` and `migrate:rollback` pick up
    | data migrations alongside schema migrations. Both share the migrations
    | table and run in timestamp order, so a data migration always runs after
    | the schema migrations it depends on.
    |
    | When disabled, or when this key is removed from a published config,
    | data migrations only run on demand with
    | `php artisan migrate --path=database/data-migrations`.
    |
    */

    'run_with_migrate' => env('DATA_MIGRATIONS_RUN_WITH_MIGRATE', true),

    /*
    |--------------------------------------------------------------------------
    | Stub
    |--------------------------------------------------------------------------
    |
    | Absolute path to the stub used by `make:data-migration`. When empty,
    | the published stub in `stubs/data-migration.stub` is used if it exists,
    | otherwise the stub shipped with this package.
    |
    */

    'stub' => null,

];
