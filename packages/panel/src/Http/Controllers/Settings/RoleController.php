<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Support\Facades\LunarAccessControl;
use Lunar\Panel\Http\Controllers\Concerns\ResolvesTableExtensions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Roles are spatie models scoped to the staff guard; the manifest supplies
 * which roles are first-party (undeletable) and how permissions group.
 */
class RoleController
{
    use ResolvesTableExtensions;

    /** @var array<int, array{key: string, label: string, width?: string, align?: string}> */
    protected array $columns = [];

    public function index(Request $request): Response
    {
        $this->columns = [
            ['key' => 'name', 'label' => __('panel::roles.column_name'), 'width' => 'minmax(0, 1.4fr)'],
            ['key' => 'permissions_count', 'label' => __('panel::roles.column_permissions'), 'width' => '120px', 'align' => 'right'],
            ['key' => 'staff_count', 'label' => __('panel::roles.column_staff'), 'width' => '100px', 'align' => 'right'],
        ];

        $guard = LunarAccessControl::getAuthGuard();
        $firstParty = LunarAccessControl::getBaseRoles();
        $resolver = $this->resolveTable('roles.index');

        $roles = Role::query()
            ->where('guard_name', $guard)
            ->withCount('permissions')
            ->tap(fn ($query) => $resolver->applyColumnQueries($query))
            ->tap(fn ($query) => $resolver->applyFilters($query, $request))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString()
            ->through(function (Role $role) use ($resolver, $firstParty): array {
                $row = [
                    'id' => $role->id,
                    'name' => $role->name,
                    'firstParty' => in_array($role->name, $firstParty, true),
                    'permissions_count' => (int) $role->getAttribute('permissions_count'),
                    'staff_count' => $role->users()->count(),
                    'urls' => [
                        'edit' => route('panel.settings.roles.edit', $role),
                    ],
                    '_actions' => $resolver->resolveRowActionUrls($role),
                ];

                foreach ($resolver->getColumnKeys() as $key) {
                    $row[$key] = $role->getAttribute($key);
                }

                return $row;
            });

        return Inertia::render('settings/roles/Index', [
            'roles' => $roles,
            ...$this->tableProps($resolver, $this->columns, $request),
            'urls' => [
                'index' => route('panel.settings.roles.index'),
                'store' => route('panel.settings.roles.store'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $guard = LunarAccessControl::getAuthGuard();

        // Role names are stored slugged, so validate uniqueness on the stored form.
        $request->merge(['name' => Str::slug((string) $request->input('name'))]);

        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique(config('permission.table_names.roles', 'roles'), 'name')
                    ->where('guard_name', $guard),
            ],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => $guard,
        ]);

        return redirect()
            ->route('panel.settings.roles.edit', $role)
            ->with('success', __('panel::roles.flash_created'));
    }

    public function edit(Role $role): Response
    {
        $this->ensureStaffGuard($role);

        $assigned = $role->permissions()->pluck('name');

        return Inertia::render('settings/roles/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'firstParty' => in_array($role->name, LunarAccessControl::getBaseRoles(), true),
                'permissions' => $assigned,
                'staff_count' => $role->users()->count(),
            ],
            // Grouped manifest permissions: parents with their children.
            'permissionGroups' => LunarAccessControl::getGroupedPermissions(true)->map(fn ($permission) => [
                'handle' => $permission->handle,
                'label' => $permission->transLabel(),
                'description' => $permission->transDescription(),
                'children' => $permission->children->map(fn ($child) => [
                    'handle' => $child->handle,
                    'label' => $child->transLabel(),
                    'description' => $child->transDescription(),
                ])->values(),
            ])->values(),
            'urls' => [
                'update' => route('panel.settings.roles.update', $role),
                'destroy' => route('panel.settings.roles.destroy', $role),
                'index' => route('panel.settings.roles.index'),
            ],
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->ensureStaffGuard($role);

        $guard = LunarAccessControl::getAuthGuard();

        $validated = $request->validate([
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => [Rule::in(LunarAccessControl::getPermissions(true)->pluck('handle')->all())],
        ]);

        $permissions = collect($validated['permissions'] ?? [])
            ->map(fn (string $handle) => Permission::findOrCreate($handle, $guard));

        $role->syncPermissions($permissions);

        return back()->with('success', __('panel::roles.flash_updated'));
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->ensureStaffGuard($role);

        if (in_array($role->name, LunarAccessControl::getBaseRoles(), true)) {
            return back()->with('error', __('panel::roles.delete_blocked_first_party'));
        }

        if ($role->users()->exists()) {
            return back()->with('error', __('panel::roles.delete_blocked_staff'));
        }

        $role->delete();

        return redirect()->route('panel.settings.roles.index')->with('success', __('panel::roles.flash_deleted'));
    }

    /** Roles from other guards are not part of the panel's world. */
    protected function ensureStaffGuard(Role $role): void
    {
        abort_unless($role->guard_name === LunarAccessControl::getAuthGuard(), 404);
    }
}
