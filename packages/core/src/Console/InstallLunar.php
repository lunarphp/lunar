<?php

namespace Lunar\Core\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\CollectionGroup;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\ProductType;
use Lunar\Core\Models\Region;
use Lunar\Core\Models\Staff;
use Lunar\Core\Models\TaxClass;
use Lunar\Core\Models\TaxZone;

use function Laravel\Prompts\confirm;

class InstallLunar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lunar:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Lunar';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->components->info('Installing Lunar...');

        $this->components->info('Publishing configuration...');

        if (! $this->configExists('lunar')) {
            $this->publishConfiguration();
        } else {
            if ($this->shouldOverwriteConfig()) {
                $this->components->info('Overwriting configuration file...');
                $this->publishConfiguration(forcePublish: true);
            } else {
                $this->components->info('Existing configuration was not overwritten');
            }
        }

        if (confirm('Run database migrations?')) {
            $this->call('migrate');
        }

        DB::transaction(function () {
            /** @var class-string<Staff> $staffModel */
            $staffModel = config('lunar.staff.model', Staff::class);

            if (! $staffModel::whereAdmin(true)->exists()) {
                $this->components->info('First create a lunar admin user');
                $this->call('lunar:create-admin');
            }

            if (! Country::count()) {
                $this->components->info('Importing countries');
                $this->call('lunar:import:address-data');
            }

            if (! Channel::whereDefault(true)->exists()) {
                $this->components->info('Setting up default channel');

                Channel::create([
                    'name' => 'Webstore',
                    'handle' => 'webstore',
                    'default' => true,
                    'url' => 'http://localhost',
                ]);
            }

            if (! Location::whereDefault(true)->exists()) {
                $this->components->info('Setting up default location');

                Location::create([
                    'name' => 'Default',
                    'handle' => 'default',
                    'default' => true,
                ]);
            }

            if (! Language::count()) {
                $this->components->info('Adding default language');

                Language::create([
                    'code' => 'en',
                    'name' => 'English',
                    'default' => true,
                ]);
            }

            if (! Currency::whereDefault(true)->exists()) {
                $this->components->info('Adding a default currency (USD)');

                Currency::create([
                    'code' => 'USD',
                    'name' => 'US Dollar',
                    'exchange_rate' => 1,
                    'decimal_places' => 2,
                    'default' => true,
                    'enabled' => true,
                ]);
            }

            if (! CustomerGroup::whereDefault(true)->exists()) {
                $this->components->info('Adding a default customer group.');

                CustomerGroup::create([
                    'name' => 'Retail',
                    'handle' => 'retail',
                    'default' => true,
                ]);
            }

            if (! CollectionGroup::count()) {
                $this->components->info('Adding an initial collection group');

                CollectionGroup::create([
                    'name' => 'Main',
                    'handle' => 'main',
                ]);
            }

            if (! TaxClass::count()) {
                $this->components->info('Adding a default tax class.');

                TaxClass::create([
                    'name' => 'Default Tax Class',
                    'default' => true,
                ]);
            }

            if (! TaxZone::count()) {
                $this->components->info('Adding a default tax zone.');

                $taxZone = TaxZone::create([
                    'name' => 'Default Tax Zone',
                    'zone_type' => 'country',
                    'default' => true,
                    'active' => true,
                ]);
                $taxZone->countries()->createMany(
                    Country::get()->map(fn ($country) => [
                        'country_id' => $country->id,
                    ])
                );
            }

            if (! ProductType::count()) {
                $this->components->info('Adding a product type.');

                ProductType::create([
                    'name' => 'Stock',
                ]);
            }

            if (! Region::whereDefault(true)->exists()) {
                $this->components->info('Adding a default region.');

                // The default region is the catch-all; specific regions list
                // their own countries, so it needs none assigned.
                Region::create([
                    'name' => 'Default',
                    'handle' => 'default',
                    'channel_id' => Channel::whereDefault(true)->value('id'),
                    'currency_id' => Currency::whereDefault(true)->value('id'),
                    'language_id' => Language::whereDefault(true)->value('id'),
                    'tax_zone_id' => TaxZone::whereDefault(true)->value('id'),
                    'default' => true,
                ]);
            }
        });

        if ($this->commandExists('filament:assets')) {
            $this->components->info('Publishing Filament assets');
            $this->call('filament:assets');
        }

        if ($this->commandExists('lunar:panel:install') && confirm('Install the Lunar panel (publish its config and assets)?')) {
            $this->call('lunar:panel:install');
        }

        $this->components->info('Lunar is now installed');

        if (confirm('Would you like to show some love by giving us a star on GitHub?')) {
            match (PHP_OS_FAMILY) {
                'Darwin' => exec('open https://github.com/lunarphp/lunar'),
                'Linux' => exec('xdg-open https://github.com/lunarphp/lunar'),
                'Windows' => exec('start https://github.com/lunarphp/lunar'),
            };

            $this->components->info('Thank you!');
        }
    }

    /**
     * Only panel-specific steps run through this guard — the panels are
     * optional, so their commands may not be registered at all.
     */
    private function commandExists(string $name): bool
    {
        return $this->getApplication()?->has($name) ?? false;
    }

    /**
     * Checks if config exists given a filename.
     */
    private function configExists(string $fileName): bool
    {
        if (! File::isDirectory(config_path($fileName))) {
            return false;
        }

        return ! empty(File::allFiles(config_path($fileName)));
    }

    /**
     * Returns a prompt if config exists and ask to override it.
     */
    private function shouldOverwriteConfig(): bool
    {
        return confirm(
            'Config file already exists. Do you want to overwrite it?',
            false
        );
    }

    /**
     * Publishes configuration for the Service Provider.
     */
    private function publishConfiguration(bool $forcePublish = false): void
    {
        $params = [
            '--tag' => 'lunar',
        ];

        if ($forcePublish === true) {
            $params['--force'] = true;
        }

        $this->call('vendor:publish', $params);
    }
}
