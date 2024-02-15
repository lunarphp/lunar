<?php

namespace Lunar\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Lunar\Facades\DB;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;

class MigrateGetCandy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lunar:migrate:getcandy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate GetCandy into Lunar';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $tableNames = collect(
            DB::connection()->getDoctrineSchemaManager()->listTableNames()
        );

        $tables = $tableNames->filter(fn ($table) => str_contains($table, 'getcandy_'));

        $lunarTables = $tableNames->filter(fn ($table) => str_contains($table, 'lunar_'));

        if ($tables->count() && ! $lunarTables->count()) {
            $this->migrateTableNames($tables);
        }

        $this->components->info('Updating Polymorphic relationships');

        $prefix = config('lunar.database.table_prefix');

        // Tables with polymorphic relations...
        $tables = [
            'media' => [
                'model_type',
            ],
            "{$prefix}urls" => [
                'element_type',
            ],
            "{$prefix}taggables" => [
                'taggable_type',
            ],
            "{$prefix}prices" => [
                'priceable_type',
            ],
            "{$prefix}order_lines" => [
                'purchasable_type',
            ],
            "{$prefix}channelables" => [
                'channelable_type',
            ],
            "{$prefix}cart_lines" => [
                'purchasable_type',
            ],
            "{$prefix}attributes" => [
                'attribute_type',
                'type',
            ],
            "{$prefix}attribute_groups" => [
                'attributable_type',
            ],
            "{$prefix}attributables" => [
                'attributable_type',
            ],
        ];

        foreach ($tables as $table => $rows) {
            $this->components->info("Updating {$table}");
            DB::transaction(function () use ($table, $rows) {
                foreach ($rows as $row) {
                    DB::table($table)->update([
                        $row => DB::RAW(
                            "REPLACE({$row}, 'GetCandy', 'Lunar')"
                        ),
                    ]);
                }
            });
        }

        $this->components->info('Updating attribute data');

        $tables = [
            'products',
            'product_variants',
            'customers',
            'collections',
        ];

        foreach ($tables as $table) {
            $tableName = $prefix.$table;

            $this->components->info("Migrating {$tableName}");

            DB::table($tableName)->update([
                'attribute_data' => DB::RAW(
                    "REPLACE(attribute_data, 'GetCandy', 'Lunar')"
                ),
            ]);
        }

        exit(self::SUCCESS);
    }

    protected function migrateTableNames($tables)
    {
        try {
            $adminMigrations = collect(File::files(
                __DIR__.'/../../../../admin/database/migrations'
            ));
        } catch (DirectoryNotFoundException $e) {
            $adminMigrations = collect();
        }

        $migrations = collect(File::files(
            __DIR__.'/../../../database/migrations'
        ))->merge($adminMigrations)->map(function ($file) {
            return $file->getBasename('.'.$file->getExtension());
        });

        $this->components->line('Removing old migrations');

        DB::table('migrations')->whereIn('migration', $migrations)->delete();

        $this->call('migrate');

        Schema::disableForeignKeyConstraints();

        foreach ($tables as $table) {
            $old = $table;
            $new = str_replace('getcandy_', 'lunar_', $table);

            if (! Schema::hasTable($old) || ! Schema::hasTable($new)) {
                continue;
            }

            $this->components->info("Migrating {$old} into {$new}");

            if ($old == 'getcandy_products') {
                if (Schema::hasColumn('getcandy_products', 'brand')) {
                    $brands = DB::table('getcandy_products')->select('brand')->distinct()->get();

                    DB::table('lunar_brands')->insert(
                        $brands->filter()->map(function ($brand) {
                            return [
                                'name' => $brand->brand,
                            ];
                        })->toArray()
                    );
                }
            }

            $brands = DB::table('lunar_brands')->get();

            DB::table($old)->orderBy('id')->chunk(100, function ($rows) use ($new, $brands) {
                $insert = [];

                foreach ($rows as $row) {
                    $data = (array) $row;
                    if (! empty($data['brand'])) {
                        $brand = $brands->first(function ($brand) use ($data) {
                            return $brand->name == $data['brand'];
                        });
                        $data['brand_id'] = $brand?->id ?: $brands->first()->id;
                        unset($data['brand']);
                    }
                    $insert[] = $data;
                }

                DB::table($new)->insert($insert);
            });

            $this->components->info("Migrated {$new}");
        }

        Schema::enableForeignKeyConstraints();

        $this->components->info('Migration finished, you can safely delete the old getcandy_ tables.');
    }
}
