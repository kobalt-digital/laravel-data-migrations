<?php

namespace Kobalt\DataMigrations\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Kobalt\DataMigrations\DataMigrations;

class MakeDataMigrationCommand extends Command
{
    protected $signature = 'make:data-migration {name : The name of the data migration}';

    protected $description = 'Create a new data migration file';

    public function handle(Filesystem $files): int
    {
        $directory = DataMigrations::path();
        $fileName = now()->format('Y_m_d_His').'_'.Str::snake(trim($this->argument('name'))).'.php';
        $filePath = $directory.DIRECTORY_SEPARATOR.$fileName;

        if ($files->exists($filePath)) {
            $this->components->error("Data migration [{$filePath}] already exists.");

            return self::FAILURE;
        }

        $files->ensureDirectoryExists($directory);
        $files->put($filePath, $files->get(DataMigrations::stubPath()));

        $this->components->info("Data migration [{$filePath}] created successfully.");

        return self::SUCCESS;
    }
}
