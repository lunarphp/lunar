<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Models\Staff;
use Lunar\Core\Support\Facades\LunarAccessControl;
use Lunar\Panel\Http\Controllers\Concerns\ResolvesTableExtensions;

class StaffIndexController
{
    use ResolvesTableExtensions;

    /** @var array<int, array{key: string, label: string, width?: string, align?: string}> */
    protected array $columns = [];

    public function index(Request $request): Response
    {
        $this->columns = [
            ['key' => 'full_name', 'label' => __('panel::staff.column_name'), 'width' => 'minmax(0, 1.3fr)'],
            ['key' => 'email', 'label' => __('panel::staff.column_email'), 'width' => 'minmax(0, 1.3fr)'],
            ['key' => 'roles', 'label' => __('panel::staff.column_roles'), 'width' => 'minmax(0, 1fr)'],
        ];

        $resolver = $this->resolveTable('staff.index');

        $staff = Staff::query()
            ->with('roles:id,name')
            ->when($request->filled('q'), function ($query) use ($request, $resolver) {
                $term = $request->string('q')->value();

                $query->where(function ($query) use ($term, $resolver) {
                    $query->search($term);
                    $resolver->applySearchQueries($query, $term);
                });
            })
            ->tap(fn ($query) => $resolver->applyColumnQueries($query))
            ->tap(fn ($query) => $resolver->applyFilters($query, $request))
            ->orderBy('first_name')
            ->paginate(25)
            ->withQueryString()
            ->through(function (Staff $member) use ($resolver): array {
                $row = [
                    'id' => $member->id,
                    'full_name' => $member->full_name,
                    'first_name' => $member->first_name,
                    'last_name' => $member->last_name,
                    'email' => $member->email,
                    'admin' => $member->admin,
                    'roles' => $member->roles->pluck('name'),
                    'urls' => [
                        'edit' => route('panel.settings.staff.edit', $member),
                    ],
                    '_actions' => $resolver->resolveRowActionUrls($member),
                ];

                foreach ($resolver->getColumnKeys() as $key) {
                    $row[$key] = $member->getAttribute($key);
                }

                return $row;
            });

        return Inertia::render('settings/staff/Index', [
            'staff' => $staff,
            ...$this->tableProps($resolver, $this->columns, $request),
            'roles' => LunarAccessControl::getRoles(true)->map(fn ($role) => [
                'handle' => $role->handle,
                'label' => $role->transLabel(),
            ])->values(),
            'filters' => $request->only(['q']),
            'urls' => [
                'index' => route('panel.settings.staff.index'),
                'store' => route('panel.settings.staff.store'),
            ],
        ]);
    }
}
