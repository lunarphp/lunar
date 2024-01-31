<?php

namespace Lunar\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Lunar\Facades\DB;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Hub\AdminHubServiceProvider;
use Lunar\Models\Attribute;
use Lunar\Models\AttributeGroup;
use Lunar\Models\Channel;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Models\ProductType;
use Lunar\Models\TaxClass;

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
        $this->newLine();
        $this->comment('Installing Lunar...');

        $this->newLine();
        $this->info('Publishing configuration...');

        if (! $this->configExists('lunar')) {
            $this->publishConfiguration();
        } else {
            if ($this->shouldOverwriteConfig()) {
                $this->line('Overwriting configuration file...');
                $this->publishConfiguration($force = true);
            } else {
                $this->line('Existing configuration was not overwritten');
            }
        }

        if ($this->confirm('Run database migrations?', true)) {
            $this->call('migrate');
        }

        DB::transaction(function () {
            if (! Country::count()) {
                $this->info('Importing countries');
                $this->call('lunar:import:address-data');
            }

            if (! Channel::whereDefault(true)->exists()) {
                $this->info('Setting up default channel');

                Channel::create([
                    'name' => 'Webstore',
                    'handle' => 'webstore',
                    'default' => true,
                    'url' => 'http://localhost',
                ]);
            }

            if (! Language::count()) {
                $this->info('Adding default language');

                Language::create([
                    'code' => 'en',
                    'name' => 'English',
                    'default' => true,
                ]);
            }

            if (! Currency::whereDefault(true)->exists()) {
                $this->info('Adding a default currency (USD)');

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
                $this->info('Adding a default customer group.');

                CustomerGroup::create([
                    'name' => 'Retail',
                    'handle' => 'retail',
                    'default' => true,
                ]);
            }

            if (! CollectionGroup::count()) {
                $this->info('Adding an initial collection group');

                CollectionGroup::create([
                    'name' => 'Main',
                    'handle' => 'main',
                ]);
            }

            if (! TaxClass::count()) {
                $this->info('Adding a default tax class.');

                TaxClass::create([
                    'name' => 'Default Tax Class',
                    'default' => true,
                ]);
            }

            if (! Attribute::count()) {
                $this->info('Setting up initial attributes');

                $group = AttributeGroup::create([
                    'attributable_type' => Product::class,
                    'name' => collect([
                        'en' => 'Details',
                    ]),
                    'handle' => 'details',
                    'position' => 1,
                ]);

                $collectionGroup = AttributeGroup::create([
                    'attributable_type' => Collection::class,
                    'name' => collect([
                        'en' => 'Details',
                    ]),
                    'handle' => 'collection_details',
                    'position' => 1,
                ]);

                Attribute::create([
                    'attribute_type' => Product::class,
                    'attribute_group_id' => $group->id,
                    'position' => 1,
                    'name' => [
                        'en' => 'Name',
                    ],
                    'handle' => 'name',
                    'section' => 'main',
                    'type' => TranslatedText::class,
                    'required' => true,
                    'default_value' => null,
                    'configuration' => [
                        'richtext' => false,
                    ],
                    'system' => true,
                ]);

                Attribute::create([
                    'attribute_type' => Collection::class,
                    'attribute_group_id' => $collectionGroup->id,
                    'position' => 1,
                    'name' => [
                        'en' => 'Name',
                    ],
                    'handle' => 'name',
                    'section' => 'main',
                    'type' => TranslatedText::class,
                    'required' => true,
                    'default_value' => null,
                    'configuration' => [
                        'richtext' => false,
                    ],
                    'system' => true,
                ]);

                Attribute::create([
                    'attribute_type' => Product::class,
                    'attribute_group_id' => $group->id,
                    'position' => 2,
                    'name' => [
                        'en' => 'Description',
                    ],
                    'handle' => 'description',
                    'section' => 'main',
                    'type' => TranslatedText::class,
                    'required' => false,
                    'default_value' => null,
                    'configuration' => [
                        'richtext' => true,
                    ],
                    'system' => false,
                ]);

                Attribute::create([
                    'attribute_type' => Collection::class,
                    'attribute_group_id' => $collectionGroup->id,
                    'position' => 2,
                    'name' => [
                        'en' => 'Description',
                    ],
                    'handle' => 'description',
                    'section' => 'main',
                    'type' => TranslatedText::class,
                    'required' => false,
                    'default_value' => null,
                    'configuration' => [
                        'richtext' => true,
                    ],
                    'system' => false,
                ]);
            }

            if (! ProductType::count()) {
                $this->info('Adding a product type.');

                $type = ProductType::create([
                    'name' => 'Stock',
                ]);

                $type->attributables()->attach(
                    Attribute::whereAttributeType(Product::class)->get()->pluck('id')
                );
            }
        });

        if ($this->isHubInstalled()) {
            $this->newLine();
            $this->line('Installing Admin Hub.');
            $this->call('lunar:hub:install');
        }

        $this->newLine();
        $this->comment('Lunar is now installed 🚀');
        $this->newLine();

        $this->line('Please show some love for Lunar by giving us a star on GitHub ⭐️');
        $this->info('https://github.com/lunarphp/lunar');
        $this->newLine(3);
    }

    /**
     * Checks if config exists given a filename.
     *
     * @param  string  $fileName
     */
    private function configExists($fileName): bool
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
        return $this->confirm(
            'Config file already exists. Do you want to overwrite it?',
            false
        );
    }

    /**
     * Publishes configuration for the Service Provider.
     *
     * @param  bool  $forcePublish
     */
    private function publishConfiguration($forcePublish = false): void
    {
        $params = [
            '--provider' => "Lunar\LunarServiceProvider",
            '--tag' => 'lunar',
        ];

        if ($forcePublish === true) {
            $params['--force'] = true;
        }

        $this->call('vendor:publish', $params);
    }

    /**
     * Determines if the admin hub is installed.
     *
     * @return bool
     */
    private function isHubInstalled()
    {
        return class_exists(AdminHubServiceProvider::class);
    }
}
