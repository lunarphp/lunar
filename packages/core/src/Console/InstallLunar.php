<?php

namespace Lunar\Console;

use Lunar\FieldTypes\TranslatedText;
use Lunar\Hub\Models\Staff;
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
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

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
    protected $description = 'Install the GetCandy';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->warn('**************************************************************************');
        $this->warn('*                              WARNING                                   *');
        $this->warn('*    We take security very seriously in Lunar and every effort is     *');
        $this->warn('*          made to stay in line with security best practices.            *');
        $this->warn('*                                                                        *');
        $this->warn('*   In order to provide rich search functionality, some sensitive data   *');
        $this->warn('*    is likely to be indexed in the search engine. Depending on your     *');
        $this->warn('*     search engine of choice, you must ensure this data is secure.      *');
        $this->warn('*                                                                        *');
        $this->warn('* Lunar accepts no liability for compromised data as a result of your *');
        $this->warn('*  storefront not following guidelines set out by third party providers. *');
        $this->warn('*                                                                        *');
        $this->warn('*      Find out more: https://docs.lunarphp.io/securing-your-site        *');
        $this->warn('**************************************************************************');

        $confirmed = $this->confirm('I understand, lets do this 🚀');

        if (! $confirmed) {
            $this->info('😔 Understood, if you have concerns, please reach out to us on Discord, https://discord.gg/v6qVWaf');

            return;
        }

        DB::transaction(function () {
            $this->info('Installing GetCandy...');

            $this->info('Publishing configuration...');

            if (! $this->configExists('lunar')) {
                $this->publishConfiguration();
            } else {
                if ($this->shouldOverwriteConfig()) {
                    $this->info('Overwriting configuration file...');
                    $this->publishConfiguration($force = true);
                } else {
                    $this->info('Existing configuration was not overwritten');
                }
            }

            $this->info('Publishing hub assets');

            if (! Country::count()) {
                $this->info('Importing countries');
                $this->call('lunar:import:address-data');
            }

            if (! Channel::whereDefault(true)->exists()) {
                $this->info('Setting up default channel');

                Channel::create([
                    'name'    => 'Webstore',
                    'handle'  => 'webstore',
                    'default' => true,
                    'url'     => 'localhost',
                ]);
            }

            if (! Staff::whereAdmin(true)->exists()) {
                $this->info('Create an admin user');

                $firstname = $this->ask('Whats your first name?');
                $lastname = $this->ask('Whats your last name?');
                $email = $this->ask('Whats your email address?');
                $password = $this->secret('Enter a password');

                Staff::create([
                    'firstname' => $firstname,
                    'lastname'  => $lastname,
                    'email'     => $email,
                    'password'  => bcrypt($password),
                    'admin'     => true,
                ]);
            }

            if (! Language::count()) {
                $this->info('Adding default language');

                Language::create([
                    'code'    => 'en',
                    'name'    => 'English',
                    'default' => true,
                ]);
            }

            if (! Currency::whereDefault(true)->exists()) {
                $this->info('Adding a default currency (USD)');

                Currency::create([
                    'code'           => 'USD',
                    'name'           => 'US Dollar',
                    'exchange_rate'  => 1,
                    'decimal_places' => 2,
                    'default'        => true,
                    'enabled'        => true,
                ]);
            }

            if (! CustomerGroup::whereDefault(true)->exists()) {
                $this->info('Adding a default customer group.');

                CustomerGroup::create([
                    'name'    => 'Retail',
                    'handle'  => 'retail',
                    'default' => true,
                ]);
            }

            if (! CollectionGroup::count()) {
                $this->info('Adding an initial collection group');

                CollectionGroup::create([
                    'name'   => 'Main',
                    'handle' => 'main',
                ]);
            }

            if (! TaxClass::count()) {
                $this->info('Adding a default tax class.');

                TaxClass::create([
                    'name'    => 'Default Tax Class',
                    'default' => true,
                ]);
            }

            if (! Attribute::count()) {
                $this->info('Setting up initial attributes');

                $group = AttributeGroup::create([
                    'attributable_type' => Product::class,
                    'name'              => collect([
                        'en' => 'Details',
                    ]),
                    'handle'   => 'details',
                    'position' => 1,
                ]);

                $collectionGroup = AttributeGroup::create([
                    'attributable_type' => Collection::class,
                    'name'              => collect([
                        'en' => 'Details',
                    ]),
                    'handle'   => 'collection_details',
                    'position' => 1,
                ]);

                Attribute::create([
                    'attribute_type'     => Product::class,
                    'attribute_group_id' => $group->id,
                    'position'           => 1,
                    'name'               => [
                        'en' => 'Name',
                    ],
                    'handle'        => 'name',
                    'section'       => 'main',
                    'type'          => TranslatedText::class,
                    'required'      => true,
                    'default_value' => null,
                    'configuration' => [
                        'richtext' => false,
                    ],
                    'system' => true,
                ]);

                Attribute::create([
                    'attribute_type'     => Collection::class,
                    'attribute_group_id' => $collectionGroup->id,
                    'position'           => 1,
                    'name'               => [
                        'en' => 'Name',
                    ],
                    'handle'        => 'name',
                    'section'       => 'main',
                    'type'          => TranslatedText::class,
                    'required'      => true,
                    'default_value' => null,
                    'configuration' => [
                        'richtext' => false,
                    ],
                    'system' => true,
                ]);

                Attribute::create([
                    'attribute_type'     => Product::class,
                    'attribute_group_id' => $group->id,
                    'position'           => 2,
                    'name'               => [
                        'en' => 'Description',
                    ],
                    'handle'        => 'description',
                    'section'       => 'main',
                    'type'          => TranslatedText::class,
                    'required'      => false,
                    'default_value' => null,
                    'configuration' => [
                        'richtext' => true,
                    ],
                    'system' => false,
                ]);

                Attribute::create([
                    'attribute_type'     => Collection::class,
                    'attribute_group_id' => $collectionGroup->id,
                    'position'           => 2,
                    'name'               => [
                        'en' => 'Description',
                    ],
                    'handle'        => 'description',
                    'section'       => 'main',
                    'type'          => TranslatedText::class,
                    'required'      => false,
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

                $type->mappedAttributes()->attach(
                    Attribute::whereAttributeType(Product::class)->get()->pluck('id')
                );
            }

            $this->info('Lunar is now installed.');

            if ($this->confirm('Would you like to show some love by starring the repo?')) {
                $exec = PHP_OS_FAMILY === 'Windows' ? 'start' : 'open';

                exec("{$exec} https://github.com/lunarphp/lunar");

                $this->line("Thanks, you're awesome!");
            }
        });
    }

    /**
     * Checks if config exists given a filename.
     *
     * @param  string  $fileName
     * @return bool
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
            '--provider' => "Lunar\LunarServiceProvider",
            '--tag'      => 'lunar',
        ];

        if ($forcePublish === true) {
            $params['--force'] = true;
        }

        $this->call('vendor:publish', $params);
    }
}
