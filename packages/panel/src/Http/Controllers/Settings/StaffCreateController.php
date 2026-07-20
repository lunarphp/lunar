<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Lunar\Core\Contracts\Actions\Staff\CreatesStaff;
use Lunar\Panel\Http\Requests\Settings\StaffRequest;

class StaffCreateController
{
    public function store(StaffRequest $request, CreatesStaff $createsStaff): RedirectResponse
    {
        $createsStaff->execute($request->staffAttributes());

        return redirect()->route('panel.settings.staff.index')->with('success', __('panel::staff.flash_created'));
    }
}
