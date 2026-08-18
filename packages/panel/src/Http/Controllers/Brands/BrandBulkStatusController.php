<?php

namespace Lunar\Panel\Http\Controllers\Brands;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Lunar\Core\Contracts\Actions\Brands\UpdatesBrand;
use Lunar\Core\Models\Brand;

class BrandBulkStatusController
{
    /**
     * Set the status on a selection of brands. The status arrives as a route
     * parameter constrained to the valid states; each brand is written
     * through the update action so events and activity logging fire as a
     * single edit would.
     */
    public function update(Request $request, string $status, UpdatesBrand $updatesBrand): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', Rule::exists((new Brand)->getTable(), 'id')],
        ]);

        Brand::query()
            ->whereIn('id', $validated['ids'])
            ->get()
            ->each(fn (Brand $brand) => $updatesBrand->execute($brand, ['status' => $status]));

        return back()->with('success', __('panel::brands.flash_status_updated'));
    }
}
