<?php

namespace Lunar\Panel\Http\Controllers\Discounts;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Contracts\Actions\Discounts\CreatesDiscount;
use Lunar\Core\Models\Discount;
use Lunar\Panel\Forms\FormSlices;
use Lunar\Panel\Http\Requests\Discounts\DiscountRequest;
use Lunar\Panel\Support\DiscountTypeSchema;

class DiscountCreateController
{
    public function create(DiscountTypeSchema $typeSchema, FormSlices $formSlices): Response
    {
        return Inertia::render('discounts/Create', [
            'formSliceValues' => $formSlices->values(new Discount) ?: (object) [],
            'types' => $typeSchema->all(),
            'urls' => [
                'store' => route('panel.discounts.store'),
                'index' => route('panel.discounts.index'),
            ],
        ]);
    }

    /**
     * Create the record and hand straight over to the edit page, which is
     * where the type's configuration, schedule and availability are set — the
     * same shape as collections.
     */
    public function store(Request $request, CreatesDiscount $createsDiscount, FormSlices $formSlices): RedirectResponse
    {
        $own = ['name', 'handle', 'type', 'starts_at'];

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'handle' => ['required', 'string', 'max:255', Rule::unique((new Discount)->getTable(), 'handle')],
            'type' => ['required', 'string', Rule::in(DiscountRequest::registeredTypes())],
            'starts_at' => ['required', 'date'],
            ...$formSlices->rules(Discount::class),
        ]);

        $discount = $formSlices->save($validated, fn () => $createsDiscount->execute(
            array_intersect_key($validated, array_flip($own)),
        ));

        return redirect()
            ->route('panel.discounts.edit', $discount)
            ->with('success', __('panel::discounts.flash_created'));
    }
}
