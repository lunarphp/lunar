<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Contracts\Actions\Staff\DeletesStaff;
use Lunar\Core\Contracts\Actions\Staff\UpdatesStaff;
use Lunar\Core\Exceptions\StaffActionException;
use Lunar\Core\Models\Staff;
use Lunar\Core\Support\Facades\LunarAccessControl;
use Lunar\Panel\Http\Requests\Settings\StaffRequest;

class StaffEditController
{
    public function edit(Staff $staff): Response
    {
        return Inertia::render('settings/staff/Edit', [
            'staff' => [
                'id' => $staff->id,
                'first_name' => $staff->first_name,
                'last_name' => $staff->last_name,
                'full_name' => $staff->full_name,
                'email' => $staff->email,
                'admin' => $staff->admin,
                'roles' => $staff->roles()->pluck('name'),
            ],
            'roles' => LunarAccessControl::getRoles(true)->map(fn ($role) => [
                'handle' => $role->handle,
                'label' => $role->transLabel(),
            ])->values(),
            'isSelf' => $staff->id === auth('staff')->id(),
            'isLastAdmin' => $staff->admin && ! Staff::query()->where('admin', true)->where('id', '!=', $staff->id)->exists(),
            'urls' => [
                'update' => route('panel.settings.staff.update', $staff),
                'destroy' => route('panel.settings.staff.destroy', $staff),
                'index' => route('panel.settings.staff.index'),
            ],
        ]);
    }

    public function update(StaffRequest $request, Staff $staff, UpdatesStaff $updatesStaff): RedirectResponse
    {
        try {
            $updatesStaff->execute($staff, $request->staffAttributes());
        } catch (StaffActionException) {
            return back()->with('error', __('panel::staff.last_admin_blocked'));
        }

        return redirect()->route('panel.settings.staff.index')->with('success', __('panel::staff.flash_updated'));
    }

    public function destroy(Staff $staff, DeletesStaff $deletesStaff): RedirectResponse
    {
        if ($staff->id === auth('staff')->id()) {
            return back()->with('error', __('panel::staff.delete_blocked_self'));
        }

        try {
            $deletesStaff->execute($staff);
        } catch (StaffActionException) {
            return back()->with('error', __('panel::staff.delete_blocked_last_admin'));
        }

        return redirect()->route('panel.settings.staff.index')->with('success', __('panel::staff.flash_deleted'));
    }
}
