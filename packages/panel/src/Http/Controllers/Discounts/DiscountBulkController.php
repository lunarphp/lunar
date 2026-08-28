<?php

namespace Lunar\Panel\Http\Controllers\Discounts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Lunar\Core\Contracts\Actions\Discounts\DeletesDiscount;
use Lunar\Core\Contracts\Actions\Discounts\UpdatesDiscount;
use Lunar\Core\Models\Discount;

class DiscountBulkController
{
    /**
     * End a selection of discounts now — the safe "switch these off" that
     * leaves the records, and their reporting, intact.
     */
    public function end(Request $request, UpdatesDiscount $updatesDiscount): RedirectResponse
    {
        $discounts = $this->selected($request);

        $discounts->each(fn (Discount $discount) => $updatesDiscount->execute($discount, ['ends_at' => now()]));

        return back()->with('success', __('panel::discounts.flash_bulk_ended', ['count' => $discounts->count()]));
    }

    public function destroy(Request $request, DeletesDiscount $deletesDiscount): RedirectResponse
    {
        $discounts = $this->selected($request);

        $discounts->each(fn (Discount $discount) => $deletesDiscount->execute($discount));

        return back()->with('success', __('panel::discounts.flash_bulk_deleted', ['count' => $discounts->count()]));
    }

    /**
     * Each record is loaded and written through its action so events and
     * activity logging fire exactly as a single edit would.
     *
     * @return Collection<int, Discount>
     */
    protected function selected(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', Rule::exists((new Discount)->getTable(), 'id')],
        ]);

        return Discount::query()->whereIn('id', $validated['ids'])->get();
    }
}
