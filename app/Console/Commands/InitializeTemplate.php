<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InitializeTemplate extends Command
{
    protected $signature = 'template:initialize';

    protected $description = 'Prepare the environment, application key and local SQLite database without overwriting existing values';

    public function handle(): int
    {
        if (! File::exists(app()->environmentFilePath())) {
            File::copy(base_path('.env.example'), app()->environmentFilePath());
            $this->components->info('Created .env. Run the command again to load its settings.');

            return self::SUCCESS;
        }

        if (app()->environment('production')) {
            $this->components->error('Configure production secrets and database through your deployment environment.');

            return self::FAILURE;
        }

        if (app()->configurationIsCached()) {
            $this->components->error('Run php artisan config:clear before initializing the local environment.');

            return self::FAILURE;
        }

        if (blank(config('app.key')) && $this->call('key:generate', ['--no-interaction' => true]) !== self::SUCCESS) {
            return self::FAILURE;
        }

        if (config('database.default') === 'sqlite') {
            $database = config('database.connections.sqlite.database');

            if ($database !== ':memory:' && ! File::exists($database)) {
                File::ensureDirectoryExists(dirname($database));
                File::put($database, '');
            }
        }

        $this->components->info('Local environment is ready. Existing keys and data were preserved.');

        return self::SUCCESS;
    }
}
