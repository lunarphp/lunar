<?php

namespace Lunar\Panel\Http\Controllers\Products;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Contracts\Actions\Products\DeletesProductVariant;
use Lunar\Core\Contracts\Actions\Products\UpdatesProductVariant;
use Lunar\Core\Exceptions\ProductActionException;
use Lunar\Core\Facades\Converter;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductOptionValue;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\StockLevel;
use Lunar\Core\Models\TaxClass;
use Lunar\Panel\Contracts\DraftManager;
use Lunar\Panel\Http\Requests\Products\ProductVariantRequest;
use Lunar\Panel\PanelManager;
use Lunar\Panel\Support\AttributeSchema;
use Lunar\Panel\Support\TimelineActivity;
use Lunar\Panel\Support\VariantFields;
use Spatie\Activitylog\Models\Activity;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductVariantController
{
    public function edit(
        Product $product,
        ProductVariant $productVariant,
        PanelManager $panel,
        DraftManager $drafts,
        AttributeSchema $attributeSchema,
        VariantFields $variantFields,
    ): Response {
        $product->load(['variants.values.option', 'productType:id,name']);

        $staff = $panel->user();
        $draft = $staff ? $drafts->find($productVariant, $staff) : null;

        $label = fn (ProductVariant $variant): string => $variant->values
            ->map(fn (ProductOptionValue $value) => $value->translate('name'))
            ->filter()
            ->implode(' / ') ?: (string) ($variant->sku ?? $variant->id);

        // Prev/next navigation across the product's variants, in id order.
        $siblings = $product->variants->sortBy('id')->values();
        $index = $siblings->search(fn (ProductVariant $variant) => $variant->id === $productVariant->id);

        $variant = $siblings[$index];

        $selection = $productVariant->images()->get()->keyBy('id');

        $mediaPool = $product->getMedia(config('lunar.media.collection'))->map(fn (Media $item) => [
            'id' => $item->id,
            'url' => $item->getAvailableUrl(['small']),
            'name' => $item->getCustomProperty('name'),
            'alt' => $item->getCustomProperty('alt'),
            'selected' => $selection->has($item->id),
            'position' => $selection->get($item->id)?->pivot?->position,
        ])->values();

        $activities = $productVariant->activities()
            ->with('causer')
            ->latest()
            ->limit(25)
            ->get()
            ->map(fn (Activity $activity) => TimelineActivity::toArray($activity));

        $measurements = Converter::getMeasurements();

        $levels = $productVariant->stockLevels()->get()->keyBy('location_id');

        $canDelete = $siblings->count() > 1 && ! $productVariant->hasOrderHistory();

        return Inertia::render('products/VariantEdit', [
            'product' => [
                'id' => $product->id,
                'name' => $product->translate('name'),
                'edit_url' => route('panel.products.edit', $product),
            ],
            'variant' => [
                'id' => $productVariant->id,
                'label' => $label($variant),
                'sku' => $productVariant->sku,
                'enabled' => $productVariant->enabled,
                'thumbnail' => $productVariant->getThumbnailImage() ?: null,
                'axes' => $variant->values->map(fn (ProductOptionValue $value) => [
                    'option' => $value->option->translate('name'),
                    'value' => $value->translate('name'),
                ])->values(),
                'position' => $index + 1,
                'total' => $siblings->count(),
                'prev_url' => $index > 0
                    ? route('panel.products.variants.edit', [$product, $siblings[$index - 1]])
                    : null,
                'next_url' => $index < $siblings->count() - 1
                    ? route('panel.products.variants.edit', [$product, $siblings[$index + 1]])
                    : null,
                'prices' => $productVariant->prices()->orderBy('min_quantity')->orderBy('id')->get()
                    ->map(fn (Price $price) => [
                        'id' => $price->id,
                        'currency_id' => $price->currency_id,
                        'customer_group_id' => $price->customer_group_id,
                        'min_quantity' => $price->min_quantity,
                        'price' => $price->price,
                        'list_price' => $price->list_price,
                        'update_url' => route('panel.products.variants.prices.update', [$product, $productVariant, $price]),
                        'destroy_url' => route('panel.products.variants.prices.destroy', [$product, $productVariant, $price]),
                    ])->values(),
                'stock' => [
                    'aggregate' => [
                        'on_hand' => $productVariant->stock_on_hand,
                        'incoming' => $productVariant->stock_incoming,
                        'committed' => $productVariant->stock_committed,
                        'reserved' => $productVariant->stock_reserved,
                        'unavailable' => $productVariant->stock_unavailable,
                        'available' => $productVariant->stock_available,
                    ],
                    'levels' => Location::query()->orderByDesc('default')->orderBy('name')->get()
                        ->map(function (Location $location) use ($levels) {
                            /** @var ?StockLevel $level */
                            $level = $levels->get($location->id);

                            return [
                                'location_id' => $location->id,
                                'location_name' => $location->name,
                                'default' => (bool) $location->default,
                                'on_hand' => (int) ($level?->on_hand ?? 0),
                                'incoming' => (int) ($level?->incoming ?? 0),
                                'committed' => (int) ($level?->committed ?? 0),
                                'unavailable' => (int) ($level?->unavailable ?? 0),
                            ];
                        })->values(),
                ],
            ],
            'variantValues' => $variantFields->values($productVariant),
            'attributeGroups' => $attributeSchema->groups($productVariant),
            'mediaPool' => $mediaPool,
            'draft' => $draft ? [
                'data' => $draft->data,
                'updated_at' => $draft->updated_at,
            ] : null,
            'languages' => Language::query()
                ->orderByDesc('default')
                ->orderBy('code')
                ->get(['id', 'code', 'name', 'default']),
            'currencies' => Currency::query()->where('enabled', true)
                ->orderByDesc('default')->orderBy('code')
                ->get(['id', 'code', 'decimal_places', 'default']),
            'customerGroups' => CustomerGroup::query()->orderBy('name')->get(['id', 'name']),
            'taxClasses' => TaxClass::query()->orderBy('name')->get(['id', 'name']),
            'measurements' => [
                'length' => array_keys($measurements['length'] ?? []),
                'weight' => array_keys($measurements['weight'] ?? []),
            ],
            'activities' => $activities,
            'canDelete' => $canDelete,
            // Why deletion is blocked, so the UI can explain the disabled button.
            // Order history takes precedence over the last-variant rule.
            'deleteBlockedReason' => match (true) {
                $canDelete => null,
                $productVariant->hasOrderHistory() => 'order_history',
                default => 'last_variant',
            },
            'urls' => [
                'productEdit' => route('panel.products.edit', $product),
                'activityLog' => route('panel.settings.activity-log.index', ['subject_type' => $productVariant->getMorphClass()]),
                'update' => route('panel.products.variants.update', [$product, $productVariant]),
                'destroy' => route('panel.products.variants.destroy', [$product, $productVariant]),
                'draft' => route('panel.products.variants.draft.update', [$product, $productVariant]),
                'draftCommit' => route('panel.products.variants.draft.commit', [$product, $productVariant]),
                'pricesStore' => route('panel.products.variants.prices.store', [$product, $productVariant]),
                'stockAdjust' => route('panel.products.variants.stock.adjust', [$product, $productVariant]),
                'mediaSync' => route('panel.products.variants.media.sync', [$product, $productVariant]),
            ],
        ]);
    }

    public function update(
        ProductVariantRequest $request,
        Product $product,
        ProductVariant $productVariant,
        UpdatesProductVariant $updatesProductVariant,
        VariantFields $variantFields,
    ): RedirectResponse {
        $updatesProductVariant->execute(
            $productVariant,
            $variantFields->commitPayload($productVariant, $request->variantAttributes()),
        );

        return back()->with('success', __('panel::products.flash_variant_updated'));
    }

    public function destroy(
        Product $product,
        ProductVariant $productVariant,
        DeletesProductVariant $deletesProductVariant,
    ): RedirectResponse {
        try {
            $deletesProductVariant->execute($productVariant);
        } catch (ProductActionException) {
            return back()->with('error', __('panel::products.flash_variant_delete_protected'));
        }

        return redirect()
            ->route('panel.products.edit', $product)
            ->with('success', __('panel::products.flash_variant_deleted'));
    }
}
