<?php

namespace Lunar\Panel\Http\Controllers\ProductTypes;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Contracts\Actions\ProductTypes\DeletesProductType;
use Lunar\Core\Contracts\Actions\ProductTypes\UpdatesProductType;
use Lunar\Core\Exceptions\ProductTypeActionException;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductType;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\TaxClass;
use Lunar\Panel\Contracts\DraftManager;
use Lunar\Panel\Http\Requests\ProductTypes\ProductTypeRequest;
use Lunar\Panel\PanelManager;
use Lunar\Panel\Support\AttributeSchema;
use Lunar\Panel\Support\Media\MediaGroups;
use Lunar\Panel\Support\TimelineActivity;
use Spatie\Activitylog\Models\Activity;

class ProductTypeEditController
{
    public function edit(ProductType $productType, PanelManager $panel, DraftManager $drafts, AttributeSchema $attributeSchema): Response
    {
        $productType->loadCount('products');

        $staff = $panel->user();
        $draft = $staff ? $drafts->find($productType, $staff) : null;

        $activities = $productType->activities()
            ->with('causer')
            ->latest()
            ->limit(25)
            ->get()
            ->map(fn (Activity $activity) => TimelineActivity::toArray($activity));

        // Mapped ids pluck through the filtered relations (allRelatedIds()
        // queries the pivot directly and would skip the morph filter); the
        // key is qualified because the pivot join makes a bare `id` ambiguous.
        $attributeKey = (new Attribute)->getQualifiedKeyName();

        return Inertia::render('product-types/Edit', [
            'productType' => [
                'id' => $productType->id,
                'name' => $productType->name,
                'handle' => $productType->handle,
                'status' => $productType->status->getValue(),
                'description' => $productType->description,
                'default_tax_class_id' => $productType->default_tax_class_id,
                'products_count' => (int) $productType->getAttribute('products_count'),
                'created_at' => $productType->created_at,
                'updated_at' => $productType->updated_at,
            ],
            'draft' => $draft ? [
                'data' => $draft->data,
                'updated_at' => $draft->updated_at,
            ] : null,
            'mediaGroups' => MediaGroups::for($productType, 'panel.product-types'),
            'languages' => Language::query()
                ->orderByDesc('default')
                ->orderBy('code')
                ->get(['id', 'code', 'name', 'default']),
            'attributeGroups' => $attributeSchema->groups($productType),
            'attributeValues' => $attributeSchema->values($productType) ?: (object) [],
            'productAttributeGroups' => $attributeSchema->pickerGroups(Product::morphName()),
            'variantAttributeGroups' => $attributeSchema->pickerGroups(ProductVariant::morphName()),
            'productAttributeIds' => $productType->productAttributes()->pluck($attributeKey),
            'variantAttributeIds' => $productType->variantAttributes()->pluck($attributeKey),
            'taxClasses' => TaxClass::query()->orderBy('name')->get(['id', 'name']),
            'activities' => $activities,
            'urls' => [
                'index' => route('panel.product-types.index'),
                'activityLog' => route('panel.settings.activity-log.index', ['subject_type' => $productType->getMorphClass()]),
                'update' => route('panel.product-types.update', $productType),
                'destroy' => route('panel.product-types.destroy', $productType),
                'draft' => route('panel.product-types.draft.update', $productType),
                'draftCommit' => route('panel.product-types.draft.commit', $productType),
                'manageAttributes' => route('panel.settings.attributes.index'),
            ],
        ]);
    }

    public function update(ProductTypeRequest $request, ProductType $productType, UpdatesProductType $updatesProductType): RedirectResponse
    {
        $updatesProductType->execute(
            $productType,
            $request->productTypeAttributes(),
            $request->attributeMappingIds($productType),
        );

        return back()->with('success', __('panel::product-types.flash_updated'));
    }

    public function destroy(ProductType $productType, DeletesProductType $deletesProductType): RedirectResponse
    {
        try {
            $deletesProductType->execute($productType);
        } catch (ProductTypeActionException) {
            return back()->with('error', __('panel::product-types.flash_delete_protected'));
        }

        return redirect()->route('panel.product-types.index')->with('success', __('panel::product-types.flash_deleted'));
    }
}
