<?php

namespace Lunar\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;

class MigrateGetCandy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lunar:migrate:getcandy
        {--cleanup=true : Removes the getcandy tables after successful migration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate GetCandy into Lunar';

    /**
     * Check if we need to run the application upgrade migtrations.
     *
     * @var bool
     */
    protected bool $runAppUpgradeMigrations = false;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tableNames = collect(
            DB::connection()->getDoctrineSchemaManager()->listTableNames()
        );

        $tables = $tableNames->filter(function ($table) {
            return str_contains($table, 'getcandy_');
        });

        $lunarTables = $tableNames->filter(function ($table) {
            return str_contains($table, 'lunar_');
        });

        if ($tables->count() && ! $lunarTables->count()) {
            $this->prepareAppUpgradeMigrations();
            $this->migrateTableNames($tables);
        }

        $this->info('Updating Polymorphic relationships');

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
            $this->line("Updating {$table}");
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

        $this->line('Updating attribute data');

        $tables = [
            'products',
            'product_variants',
            'customers',
            'collections',
        ];

        foreach ($tables as $table) {
            $tableName = $prefix.$table;

            $this->line("Migrating {$tableName}");

            DB::table($tableName)->update([
                'attribute_data' => DB::RAW(
                    "REPLACE(attribute_data, 'GetCandy', 'Lunar')"
                ),
            ]);
        }

        if ($this->option('cleanup')) {
            $this->cleanup();
        }

        return Command::SUCCESS;
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

        $this->line('Removing old migrations');

        DB::table('migrations')->whereIn('migration', $migrations)->delete();

        $this->call('migrate');

        if ($this->runAppUpgradeMigrations) {
            $this->call('migrate', [
                '--path' => database_path('migrations/upgrade'),
            ]);
        }

        Schema::disableForeignKeyConstraints();

        foreach ($tables as $table) {
            $old = $table;
            $new = str_replace('getcandy_', 'lunar_', $table);

            if (! Schema::hasTable($old) || ! Schema::hasTable($new)) {
                continue;
            }

            $this->line("Migrating {$old} into {$new}");

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

            $this->info("Migrated {$new}");
        }

        Schema::enableForeignKeyConstraints();

        $this->info('Migration finished.');
    }

    protected function prepareAppUpgradeMigrations(): void
    {
        $upgradePath = database_path('migrations/upgrade');

        try {
            $appUpgradeMigrations = collect(File::files($upgradePath));
        } catch (DirectoryNotFoundException $e) {
            $appUpgradeMigrations = collect();
        }

        if ($appUpgradeMigrations->isEmpty()) {
            return;
        }

        $this->runAppUpgradeMigrations = true;

        $migrations = $appUpgradeMigrations->map(function ($file) {
            return $file->getBasename('.'.$file->getExtension());
        });

        $this->line('Removing any existing upgrade migrations');

        DB::table('migrations')->whereIn('migration', $migrations)->delete();
    }

    protected function cleanup(): void
    {
        $tableNames = collect(
            DB::connection()->getDoctrineSchemaManager()->listTableNames()
        );

        $tables = $tableNames->filter(function ($table) {
            return str_starts_with($table, 'getcandy_');
        });

        Schema::disableForeignKeyConstraints();

        $removedCount = 0;
        foreach ($tables as $table) {
            $old = $table;
            $new = str_replace('getcandy_', 'lunar_', $table);

            $schemaExists = Schema::hasTable($old) && Schema::hasTable($new);
            if ($schemaExists) {
                $schemaMatchesCount = DB::table($old)->count() === DB::table($new)->count();
                if ($schemaMatchesCount) {
                    $this->line("Dropping {$old}");
                    Schema::dropIfExists($old);
                    $removedCount++;
                }
            }
        }

        Schema::enableForeignKeyConstraints();

        if ($removedCount) {
            $this->info("Cleanup finished removed {$removedCount} old tables");
        }
    }
}
