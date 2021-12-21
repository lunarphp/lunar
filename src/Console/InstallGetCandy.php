<?php

namespace GetCandy\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallGetCandy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'getcandy:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the GetCandy';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->info('Installing GetCandy...');

        $this->info('Publishing configuration...');

        if (! $this->configExists('getcandy')) {
            $this->publishConfiguration();
        } else {
            if ($this->shouldOverwriteConfig()) {
                $this->info('Overwriting configuration file...');
                $this->publishConfiguration($force = true);
            } else {
                $this->info('Existing configuration was not overwritten');
            }
        }

        $this->info('GetCandy is now installed.');
    }

    /**
     * Checks if config exists given a filename.
     *
     * @param  string  $fileName
     * @return bool
     */
    private function configExists($fileName): bool
    {
        return ! empty(File::allFiles(config_path($fileName)));
    }

    /**
     * Returns a prompt if config exists and ask to override it.
     *
     * @return bool
     */
    private function shouldOverwriteConfig(): bool
    {
        return $this->confirm(
            'Config file already exists. Do you want to overwrite it?',
            false
        );
    }

    /**
     * Publishes configuration for the Service Provider.
     *
     * @param  bool  $forcePublish
     * @return void
     */
    private function publishConfiguration($forcePublish = false): void
    {
        $params = [
            '--provider' => "GetCandy\GetCandyServiceProvider",
            '--tag' => 'getcandy',
        ];

        if ($forcePublish === true) {
            $params['--force'] = true;
        }

        $this->call('vendor:publish', $params);
    }
}
