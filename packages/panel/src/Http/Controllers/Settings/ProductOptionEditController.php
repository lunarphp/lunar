<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Contracts\Actions\ProductOptions\DeletesProductOption;
use Lunar\Core\Contracts\Actions\ProductOptions\UpdatesProductOption;
use Lunar\Core\Exceptions\ProductOptionActionException;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\ProductOption;
use Lunar\Core\Models\ProductOptionValue;
use Lunar\Panel\Http\Requests\Settings\ProductOptionRequest;
use Lunar\Panel\Support\TimelineActivity;
use Spatie\Activitylog\Models\Activity;

class ProductOptionEditController
{
    public function edit(ProductOption $productOption): Response
    {
        $collection = config('lunar.media.collection');

        $activities = $productOption->activities()
            ->with('causer')
            ->latest()
            ->limit(25)
            ->get()
            ->map(fn (Activity $activity) => TimelineActivity::toArray($activity));

        return Inertia::render('settings/product-options/Edit', [
            'productOption' => [
                'id' => $productOption->id,
                'name' => (array) ($productOption->name ?? []),
                'label' => (array) ($productOption->label ?? []),
                'handle' => $productOption->handle,
                'type' => $productOption->type,
                'shared' => $productOption->shared,
                'products_count' => $productOption->products()->count(),
                'created_at' => $productOption->created_at,
                'updated_at' => $productOption->updated_at,
            ],
            'values' => $productOption->values()
                ->withCount('variants')
                ->orderBy('position')
                ->get()
                ->map(fn (ProductOptionValue $value) => [
                    'id' => $value->id,
                    'name' => (array) ($value->name ?? []),
                    'position' => $value->position,
                    'colour' => $value->meta['colour'] ?? null,
                    'swatch' => $value->getFirstMediaUrl($collection, 'small') ?: ($value->getFirstMediaUrl($collection) ?: null),
                    'variant_count' => (int) $value->getAttribute('variants_count'),
                    // Values carried by variants cannot be removed.
                    'inUse' => (int) $value->getAttribute('variants_count') > 0,
                    'urls' => [
                        'swatch' => route('panel.settings.product-options.values.swatch.store', [$productOption, $value]),
                    ],
                ]),
            'languages' => Language::query()
                ->orderByDesc('default')
                ->orderBy('code')
                ->get(['id', 'code', 'name', 'default']),
            'hasProducts' => $productOption->products()->exists(),
            'activities' => $activities,
            'urls' => [
                'update' => route('panel.settings.product-options.update', $productOption),
                'destroy' => route('panel.settings.product-options.destroy', $productOption),
                'index' => route('panel.settings.product-options.index'),
                'products' => route('panel.products.index'),
                'activityLog' => route('panel.settings.activity-log.index', ['subject_type' => $productOption->getMorphClass()]),
            ],
        ]);
    }

    public function update(ProductOptionRequest $request, ProductOption $productOption, UpdatesProductOption $updatesProductOption): RedirectResponse
    {
        $attributes = $request->productOptionAttributes();

        // Sharing is a one-way promotion: a shared option can never be demoted
        // back to dedicated, so keep it shared regardless of the payload.
        if ($productOption->shared) {
            $attributes['shared'] = true;
        }

        try {
            $updatesProductOption->execute($productOption, $attributes);
        } catch (ProductOptionActionException) {
            return back()->with('error', __('panel::product_options.value_delete_blocked'));
        }

        return back()->with('success', __('panel::product_options.flash_updated'));
    }

    public function destroy(ProductOption $productOption, DeletesProductOption $deletesProductOption): RedirectResponse
    {
        try {
            $deletesProductOption->execute($productOption);
        } catch (ProductOptionActionException) {
            return back()->with('error', __('panel::product_options.delete_blocked'));
        }

        return redirect()->route('panel.settings.product-options.index')->with('success', __('panel::product_options.flash_deleted'));
    }
}
