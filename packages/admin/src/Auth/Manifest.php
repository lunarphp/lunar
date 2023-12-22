<?php

namespace Lunar\Admin\Auth;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Lunar\Admin\Support\DataTransferObjects\Permission;
use Lunar\Admin\Support\DataTransferObjects\Role;
use Lunar\Admin\Support\Facades\LunarPanel;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Permission\Models\Role as SpatieRole;

class Manifest
{
    /**
     * A collection of permissions loaded into the manifest.
     */
    protected ?Collection $permissions = null;

    /**
     * A collection of roles loaded into the manifest.
     */
    protected ?Collection $roles = null;

    /**
     * A collection of admins loaded into the manifest.
     */
    protected ?Collection $admins = null;

    /**
     * Returns all roles loaded in the manifest.
     */
    public function getRoles(bool $refresh = false): Collection
    {
        if (! $refresh && ! is_null($this->roles)) {
            return $this->roles;
        }

        $this->roles = collect();

        $baseRoles = $this->getBaseRoles();

        $roles = SpatieRole::where('guard_name', LunarPanel::getPanel()->getAuthGuard())->get('name');

        foreach ($roles as $role) {
            $this->roles->push(Role::make($role->name, in_array($role->name, $baseRoles)));
        }

        $last = count($baseRoles) + 10;

        $this->roles = $this->roles->sortBy(function ($role) use ($baseRoles, $last) {
            $search = array_search($role->handle, $baseRoles);

            return $search === false ? $last : $search;
        });

        return $this->roles;
    }

    /**
     * Returns all permissions loaded in the manifest.
     */
    public function getPermissions(bool $refresh = false): Collection
    {
        if (! $refresh && ! is_null($this->permissions)) {
            return $this->permissions;
        }

        $this->permissions = collect();

        $basePermissions = $this->getBasePermissions();

        $permissions = SpatiePermission::where('guard_name', LunarPanel::getPanel()->getAuthGuard())->get('name');

        foreach ($permissions as $permission) {
            $this->permissions->push(Permission::make($permission->name, in_array($permission->name, $basePermissions)));
        }

        $last = count($basePermissions) + 10;

        $this->permissions = $this->permissions->sortBy(function ($permission) use ($basePermissions, $last) {
            $search = array_search($permission->handle, $basePermissions);

            return $search === false ? $last : $search;
        });

        return $this->permissions;
    }

    /**
     * Returns permissions grouped by their handle
     * For example, settings:channel would become a child of settings.
     */
    public function getGroupedPermissions(bool $refresh = false): Collection
    {
        $permissions = clone $this->getPermissions($refresh);

        foreach ($permissions as $key => $permission) {
            $parent = $this->getParentPermission($permission);

            if ($parent) {
                $parent->children->put($permission->handle, $permission);
                $permissions->forget($key);
            }
        }

        return $permissions;
    }

    /**
     * Returns the parent permission based on handle naming.
     */
    protected function getParentPermission(Permission $permission): ?Permission
    {
        $crumbs = explode(':', $permission->handle);

        if (empty($crumbs[1])) {
            return null;
        }

        return $this->permissions->first(fn ($parent) => $parent->handle === $crumbs[0]);
    }

    /**
     * Returns the base permissions which are required by Lunar.
     */
    public function getBaseRoles(): array
    {
        return [
            'admin',
            'staff',
        ];
    }

    /**
     * Returns the base permissions which are required by Lunar.
     */
    public function getBasePermissions(): array
    {
        return [
            'settings',
            'settings:core',
            'settings:manage-staff',
            'settings:manage-attributes',
            'catalog:manage-products',
            'catalog:manage-collections',
            'catalog:manage-brands',
            'sales:manage-orders',
            'sales:manage-customers',
            'sales:manage-discounts',
        ];
    }

    /**
     * Adds admin roles to the manifest if it doesn't already exist.
     */
    public function useRoleAsAdmin(string|array $roleHandle): void
    {
        if (is_null($this->admins)) {
            $this->admins = collect();
        }

        $admins = Arr::wrap($roleHandle);
        foreach ($admins as $admin) {
            // Do we already have a admin with this handle?
            $existing = $this->admins->contains($admin);

            if (! $existing) {
                $this->admins->push($admin);
            }
        }
    }

    /**
     * Returns all admin roles loaded in the manifest.
     */
    public function getAdmin(): Collection
    {
        if (is_null($this->admins)) {
            // if no admin registered, default `admin`
            return collect('admin');
        }

        return $this->admins;
    }

    /**
     * Returns all roles excluding admin.
     */
    public function getRolesWithoutAdmin(bool $refresh = false): Collection
    {
        return $this->getRoles($refresh)->reject(fn ($r) => $this->getAdmin()->contains($r->handle));
    }
}
