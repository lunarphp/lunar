<?php

namespace GetCandy\Hub\Auth;

use Illuminate\Support\Collection;

class Manifest
{
    /**
     * A collection of permissions loaded into the manifest.
     *
     * @var Collection
     */
    protected Collection $permissions;

    /**
     * Initialise the manifest class.
     */
    public function __construct()
    {
        $this->permissions = collect($this->getBasePermissions());
    }

    /**
     * Returns all permissions loaded in the manifest.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getPermissions(): Collection
    {
        return $this->permissions;
    }

    /**
     * Returns permissions grouped by their handle
     * For example, settings:channel would become a child of settings.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getGroupedPermissions(): Collection
    {
        $permissions = clone $this->permissions;

        foreach ($permissions as $key => $permission) {
            $parent = $this->getParentPermission($permission);

            if ($parent) {
                $parent->children->push($permission);
                $permissions->forget($key);
            }
        }

        return $permissions;
    }

    /**
     * Returns the parent permission based on handle naming.
     *
     * @param Permission $permission
     *
     * @return null|\GetCandy\Hub\Acl\Permission
     */
    protected function getParentPermission(Permission $permission)
    {
        $crumbs = explode(':', $permission->handle);

        if (empty($crumbs[1])) {
            return null;
        }

        return $this->permissions->first(fn ($parent) => $parent->handle === $crumbs[0]);
    }

    /**
     * Adds a permission to the manifest if it doesn't already exist.
     *
     * @param \Closure $callback
     *
     * @return void
     */
    public function addPermission(\Closure $callback)
    {
        $permission = new Permission();
        $callback($permission);

        $permission->firstParty(false);

        // Do we already have a permission with this handle?
        $existing = $this->permissions->first(fn ($p) => $p->handle == $permission->handle);

        if (!$existing) {
            $this->permissions->push($permission);
        }
    }

    /**
     * Returns the base permissions which are required by GetCandy.
     *
     * @return array
     */
    protected function getBasePermissions(): array
    {
        return [
            new Permission(
                __('adminhub::auth.permissions.settings.name'),
                'settings',
                __('adminhub::auth.permissions.settings.description')
            ),
            new Permission(
                __('adminhub::auth.permissions.settings.core.name'),
                'settings:core',
                __('adminhub::auth.permissions.settings.core.description')
            ),
            new Permission(
                __('adminhub::auth.permissions.settings.staff.name'),
                'settings:manage-staff',
                __('adminhub::auth.permissions.settings.staff.description')
            ),
            new Permission(
                __('adminhub::auth.permissions.settings.attributes.name'),
                'settings:manage-attributes',
                __('adminhub::auth.permissions.settings.attributes.description')
            ),
            new Permission(
                __('adminhub::auth.permissions.catalogue.products.name'),
                'catalogue:manage-products',
                __('adminhub::auth.permissions.catalogue.products.description')
            ),
            new Permission(
                __('adminhub::auth.permissions.catalogue.collections.name'),
                'catalogue:manage-collections',
                __('adminhub::auth.permissions.catalogue.collections.description')
            ),
            new Permission(
                __('adminhub::auth.permissions.catalogue.orders.name'),
                'catalogue:manage-orders',
                __('adminhub::auth.permissions.catalogue.orders.description')
            ),
            new Permission(
                __('adminhub::auth.permissions.catalogue.customers.name'),
                'catalogue:manage-customers',
                __('adminhub::auth.permissions.catalogue.customers.description')
            ),
        ];
    }
}
