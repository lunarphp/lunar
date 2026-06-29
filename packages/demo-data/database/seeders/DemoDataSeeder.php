<?php

namespace Lunar\DemoData\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\DemoData\Generators\CatalogueGenerator;
use Lunar\DemoData\Generators\CustomersGenerator;
use Lunar\DemoData\Generators\FoundationGenerator;
use Lunar\DemoData\Generators\Generator;
use Lunar\DemoData\Generators\OrdersGenerator;
use Lunar\DemoData\Support\DemoContext;

class DemoDataSeeder extends Seeder
{
    protected ?DemoContext $context = null;

    /**
     * Generators run in dependency order. Each is idempotent against its own
     * data, so a re-run without --fresh is a no-op.
     *
     * @var array<class-string<Generator>>
     */
    protected array $generators = [
        FoundationGenerator::class,
        CatalogueGenerator::class,
        CustomersGenerator::class,
        OrdersGenerator::class,
    ];

    /**
     * The demo-owned (Lunar-prefixed) tables wiped by --fresh. The foundation
     * (channels, currencies, languages, customer groups, locations, tax,
     * countries, product types) is left intact — it is idempotent and shared
     * with `lunar:install`.
     *
     * @var array<int, string>
     */
    protected array $truncate = [
        'orders', 'order_lines', 'order_addresses', 'transactions',
        'fulfilments', 'fulfilment_lines', 'fulfilment_trackings',
        'customers', 'addresses', 'customer_user', 'customer_customer_group', 'customer_discount',
        'products', 'product_variants', 'prices', 'media_product_variant',
        'product_associations', 'product_options', 'product_option_values',
        'product_option_value_product_variant', 'product_product_option',
        'collections', 'collection_groups', 'collection_product', 'brand_collection',
        'collection_customer_group', 'collection_discount',
        'brands', 'brand_discount', 'channelables',
        'stock_levels', 'stock_movements', 'stock_reservations',
    ];

    public function usingContext(DemoContext $context): static
    {
        $this->context = $context;

        return $this;
    }

    public function run(): void
    {
        $context = $this->context ?? DemoContext::fromConfig();

        if ($context->fresh) {
            $this->fresh();
        }

        foreach ($this->generators as $generator) {
            $this->command?->getOutput()->writeln("  <fg=gray>-</> {$generator}");

            app($generator)->generate($context);
        }
    }

    /**
     * Wipe the demo-owned tables so the seed starts clean.
     */
    protected function fresh(): void
    {
        $prefix = (string) config('lunar.database.table_prefix');

        // Spatie media (product images) lives in an unprefixed table.
        $tables = [...array_map(fn ($table) => $prefix.$table, $this->truncate), 'media'];

        Schema::disableForeignKeyConstraints();

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }

        Schema::enableForeignKeyConstraints();

        // The users table is shared (staff/admins live there too), so target
        // only the demo accounts by their email domain rather than truncating.
        /** @var class-string<Model> $userModel */
        $userModel = config('auth.providers.users.model');
        $userModel::query()->where('email', 'like', '%@demo-store.test')->delete();
    }
}
