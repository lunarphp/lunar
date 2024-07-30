<?php

namespace Lunar\Admin\Support\Forms\Components;

use Closure;
use Exception;
use Filament\Forms\Components\Field;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Lunar\Admin\Models\Staff;
use Lunar\Admin\Support\Facades\LunarAccessControl;
use Lunar\Admin\Support\Facades\LunarPanel;
use Spatie\Permission\Models\Role;

class PermissionSelector extends Field
{
    protected string $view = 'lunarpanel::forms.components.permission-selector';

    public Closure $authorize;

    public string $rolesField = 'roles';

    protected function setUp(): void
    {
        parent::setUp();

        $this->authorize(fn () => $this->getRecord());

        $this->columnSpanFull();

        $this->hiddenLabel();

        $this->afterStateHydrated(static function (PermissionSelector $component): void {
            $component->state($component->getPermissionState());
        });

        $this->saveRelationshipsUsing(static function (PermissionSelector $component, ?array $state) {
            if (! is_array($state)) {
                $state = [];
            }

            $rolesPermissions = [];

            $selectedRoles = $component->getRolesState();

            $admins = LunarAccessControl::getAdmin()->intersect($selectedRoles);

            $filteredRoles = collect($selectedRoles)->reject(fn ($r) => $admins->contains($r));

            foreach ($filteredRoles as $role) {
                $roleModel = Role::findByName($role, LunarPanel::getPanel()->getAuthGuard());
                $rolePerms = $roleModel->getAllPermissions();

                foreach ($rolePerms as $perm) {
                    $rolesPermissions[$perm->name] = $perm->name;
                }
            }

            $groupedPermissions = LunarAccessControl::getGroupedPermissions();

            if ($admins->count()) {
                $filtered = [];
            } else {
                $filtered = $groupedPermissions->flatMap(function ($permission) use ($state) {
                    $perm = $state[$permission->handle] ?? null;

                    if ($perm !== false) {
                        if ($permission->children->count()) {
                            $childPerms = $permission->children
                                ->pluck('handle')
                                ->filter(fn ($perm) => ($state[$perm] ?? null) === true);

                            if ($perm === true) {
                                $childPerms->prepend($permission->handle);
                            }

                            return $childPerms->toArray();
                        } else {
                            if ($state[$permission->handle] === true) {
                                return [$permission->handle];
                            }
                        }
                    }
                });
            }

            $component->getAuthorizeRecord()?->syncPermissions($filtered);
        });

        $this->dehydrated(false);
    }

    public function getPermissionState(): array
    {
        $state = $this->getState();
        $isUpdated = filled($state);

        if (is_null($state)) {
            $state = LunarAccessControl::getPermissions()
                ->mapWithKeys(fn ($p) => [$p->handle => false])->toArray();
        }

        $directPermissions = $this->getAuthorizeRecord()?->getDirectPermissions()?->pluck('name') ?? collect();
        $groupedPermissions = LunarAccessControl::getGroupedPermissions();

        $rolesPermissions = [];

        $selectedRoles = $this->getRolesState();

        $admins = LunarAccessControl::getAdmin()->intersect($selectedRoles);

        $filteredRoles = collect($selectedRoles)->reject(fn ($r) => $admins->contains($r));

        // permissions from roles
        foreach ($filteredRoles as $role) {
            $roleModel = Role::findByName($role, LunarPanel::getPanel()->getAuthGuard());
            $rolePerms = $roleModel->getAllPermissions();

            foreach ($rolePerms as $perm) {
                $rolesPermissions[$perm->name] = $perm->name;
            }
        }

        if (! $isUpdated) {
            foreach ($groupedPermissions as $permission) {
                if (isset($rolesPermissions[$permission->handle])) {
                    $state[$permission->handle] = null;
                } elseif ($directPermissions->contains($permission->handle)) {
                    $state[$permission->handle] = true;
                }

                foreach ($permission->children as $childPerm) {
                    $state[$childPerm->handle] = isset($rolesPermissions[$childPerm->handle]) ? null : $directPermissions->contains($childPerm->handle);
                }
            }
        } else {
            // check grouped permission first
            foreach ($groupedPermissions as $permission) {
                $groupState = null;

                // check if permission from selected roles
                if (isset($rolesPermissions[$permission->handle])) {
                    $groupState = null;
                } else {
                    $groupState = (bool) $state[$permission->handle];
                }

                // determine if has child
                $hasChild = false;
                foreach ($permission->children as $childPerm) {
                    if (isset($rolesPermissions[$childPerm->handle])
                        || (bool) $state[$childPerm->handle]) {
                        $hasChild = true;

                        break;
                    }
                }

                $state[$permission->handle] = $hasChild && $groupState === false ? true : $groupState;

                // set proper state
                foreach ($permission->children as $childPerm) {
                    // if permission is from roles, dont bother just set as null, else if group is
                    //      true (direct): then child can either true/false only
                    //      false (not assigned): only false
                    //      null (inherited): if child perm from role then can only be null, else determine the correct state
                    $state[$childPerm->handle] = isset($rolesPermissions[$childPerm->handle]) ? null : match ($state[$permission->handle]) {
                        true => (bool) $state[$childPerm->handle],
                        false => false,
                        null => isset($rolesPermissions[$childPerm->handle]) ? null : (bool) $state[$childPerm->handle],
                    };
                }
            }
        }

        return $state;
    }

    public function getGroupedPermissions(): Collection
    {
        return LunarAccessControl::getGroupedPermissions();
    }

    public function authorize(Closure $authorize): static
    {
        $this->authorize = $authorize;

        return $this;
    }

    public function getAuthorizeRecord(): ?Authenticatable
    {
        $record = $this->evaluate($this->authorize);

        if (is_null($record)) {
            return null;
        }

        $traits = trait_uses_recursive($record);

        if (! in_array(\Spatie\Permission\Traits\HasRoles::class, $traits)) {
            throw new Exception('Not implemented \Spatie\Permission\Traits\HasRoles');
        }

        /** @var Staff $record */
        return $record;
    }

    public function roles(string $field): static
    {
        $this->rolesField = $field;

        return $this;
    }

    protected function getRolesField(): ?Field
    {
        return collect($this->getContainer()->getFlatComponents())
            ->first(fn (Field $component) => $component->getName() == $this->rolesField);
    }

    protected function getRolesState(): array
    {
        return $this->getRolesField()->getState();
    }

    public function getPermissionRoles(): array
    {
        $exclude = $this->getRolesState();

        $roles = LunarAccessControl::getRolesWithoutAdmin()
            ->filter(fn ($role) => in_array($role->handle, $exclude));

        $permissions = [];

        foreach ($roles as $role) {
            $roleModel = Role::findByName($role->handle, LunarPanel::getPanel()->getAuthGuard());

            $rolePerms = $roleModel->getAllPermissions();

            foreach ($rolePerms as $permission) {
                $permissions[$permission->name] ??= [];

                $permissions[$permission->name][$role->handle] = $role->transLabel;
            }

        }

        return $permissions;
    }
}
