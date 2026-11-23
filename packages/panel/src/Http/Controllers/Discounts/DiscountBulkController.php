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
     *
     * A discount that has already ended keeps its end date, so ending it again
     * never extends its history. A discount that has not started yet, or
     * started less than a minute ago, gets its start moved to a minute before
     * the new end: the edit form requires the end to be after the start and
     * submits both at minute precision, so a shorter window would leave the
     * discount impossible to save.
     */
    public function end(Request $request, UpdatesDiscount $updatesDiscount): RedirectResponse
    {
        $endsAt = now();
        $latestStart = $endsAt->copy()->subMinute();

        $discounts = $this->selected($request)->reject(
            fn (Discount $discount) => $discount->ends_at?->lte($endsAt)
        );

        $discounts->each(function (Discount $discount) use ($endsAt, $latestStart, $updatesDiscount) {
            $attributes = ['ends_at' => $endsAt];

            if (! $discount->starts_at?->lte($latestStart)) {
                $attributes['starts_at'] = $latestStart;
            }

            $updatesDiscount->execute($discount, $attributes);
        });

        return back()->with('success', trans_choice('panel::discounts.flash_bulk_ended', $discounts->count()));
    }

    public function destroy(Request $request, DeletesDiscount $deletesDiscount): RedirectResponse
    {
        $discounts = $this->selected($request);

        $discounts->each(fn (Discount $discount) => $deletesDiscount->execute($discount));

        return back()->with('success', trans_choice('panel::discounts.flash_bulk_deleted', $discounts->count()));
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
