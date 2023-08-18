<?php

namespace Lunar\Hub\Database\State;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Lunar\Hub\Actions\Permission\SyncRolesPermissions;
use Lunar\Hub\Models\Staff;
use Lunar\Hub\Models\StaffPermission;
use Spatie\Permission\Models\Permission;

class EnsurePermissionsAreUpgraded
{
    public $backupFile = 'tmp/state/legacy_permissions.json';

    public function prepare()
    {
        if (! $this->canPrepare()) {
            return;
        }

        $legacyPermission = StaffPermission::query()
            ->get()
            ->mapToGroups(fn (StaffPermission $item, int $key) => [$item->staff_id => $item->handle]);

        if ($legacyPermission->isEmpty()) {
            return;
        }

        Storage::put($this->backupFile, json_encode($legacyPermission, JSON_PRETTY_PRINT));
    }

    public function run()
    {
        if (! $this->canRun() || ! $this->shouldRun()) {
            return;
        }

        $permissions = null;

        try {
            app(SyncRolesPermissions::class)();

            $permissions = json_decode(Storage::get($this->backupFile), true);
        } catch (FileNotFoundException $e) {
        }

        if ($permissions) {
            $staffs = Staff::get();

            foreach ($staffs as $staff) {
                $staffPermissions = isset($permissions[$staff->id]) ? $permissions[$staff->id] : [];

                $role = $staff->admin ? 'admin' : 'staff';

                $staff->assignRole($role);

                $staff->givePermissionTo($staffPermissions);
            }
        }

        Storage::disk()->delete($this->backupFile);
    }

    protected function canPrepare()
    {
        $prefix = config('lunar.database.table_prefix');

        $hasStaffPermissionsTable = Schema::hasTable("{$prefix}staff_permissions");

        return $hasStaffPermissionsTable && StaffPermission::count();
    }

    protected function canRun()
    {
        $prefix = config('lunar.database.table_prefix');

        $hasStaffPermissionsTable = Schema::hasTable("{$prefix}staff_permissions");

        return $hasStaffPermissionsTable && StaffPermission::count();
    }

    protected function shouldRun()
    {
        $hasSpatiePermissionsTable = Schema::hasTable((new Permission())->getTable());

        return $hasSpatiePermissionsTable && ! Permission::count();
    }
}
