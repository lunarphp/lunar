<?php

namespace Lunar\Hub\Console\Commands;

use Illuminate\Console\Command;
use Lunar\Hub\Auth\Manifest;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SyncRolesPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lunar:hub:sync-roleperm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync hub roles and permissions';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Sync hub roles and permissions');

        $manifest = new Manifest();

        $permissions = $manifest->getPermissions();

        $guard = 'staff';

        foreach (['admin', 'staff'] as $roleName) {
            Role::query()->firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'staff',
            ]);
        }

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission->handle,
                'guard_name' => 'staff',
            ]);
        }
    }
}
