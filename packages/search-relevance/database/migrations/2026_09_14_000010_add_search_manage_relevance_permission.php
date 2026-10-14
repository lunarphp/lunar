<?php

use Illuminate\Support\Facades\Schema;
use Lunar\Core\Auth\Manifest;
use Lunar\Core\Database\Migration;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up()
    {
        $tableNames = config('permission.table_names');

        if (! isset($tableNames['permissions']) || ! Schema::hasTable($tableNames['permissions'])) {
            return;
        }

        Permission::firstOrCreate([
            'name' => 'search:manage-relevance',
            'guard_name' => app(Manifest::class)->getAuthGuard(),
        ]);
    }

    public function down()
    {
        $tableNames = config('permission.table_names');

        if (! isset($tableNames['permissions']) || ! Schema::hasTable($tableNames['permissions'])) {
            return;
        }

        Permission::query()->where('name', 'search:manage-relevance')->delete();
    }
};
