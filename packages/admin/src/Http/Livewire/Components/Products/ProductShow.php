<?php

namespace Lunar\Hub\Http\Livewire\Components\Products;

use Lunar\Models\ProductOption;

class ProductShow extends AbstractProduct
{
    /**
     * Called when the component is mounted.
     *
     * @return void
     */
    public function mount()
    {
        $this->variantsEnabled = $this->getVariantsCount() > 1;
        $this->variant = $this->product->variants->first();

        $this->variantAttributes = $this->parseAttributes(
            $this->availableVariantAttributes,
            $this->variant->attribute_data,
            'variantAttributes',
        );

        $variants = $this->product->variants->load('values');

        $selectedOptions = [];

        foreach ($variants as $variant) {
            foreach ($variant->values as $value) {
                $selectedOptions[$value->product_option_id][$value->id] = $value;
            }
        }

        $this->options = ProductOption::orderBy('position')->findMany(array_keys($selectedOptions));

        $this->optionValues = collect($selectedOptions)->collapse()->pluck('id')->unique()->values()->toArray();

        $options = $this->options->pluck('id')->toArray();

        foreach ($variants as $variant) {
            $optionValues = $variant->values->pluck('id', 'product_option_id')->toArray();
            $key = sha1(implode(',', $optionValues));

            $currentVariants[] = $key;

            $this->variants[$key] = array_merge(
                [
                    'labels' => collect($optionValues)
                        ->sortBy(function ($model, $key) use ($options) {
                            return array_search($key, $options);
                        })->map(function ($valueId, $optionId) use ($selectedOptions) {
                            return [
                                'option' => $this->options->where('id', $optionId)->first()->translate('name'),
                                'value' => $selectedOptions[$optionId][$valueId]->translate('name'),
                            ];
                        })->values(),
                    'basePrices' => $this->mapBasePrices($variant->prices),
                    'stock' => $variant->stock,
                    'backorder' => $variant->backorder,
                    'options' => $optionValues,
                    'id' => $variant->id,
                ],
                collect(['sku', 'gtin', 'mpn', 'ean'])->mapWithKeys(fn ($identifier) => [$identifier => @$variant->{$identifier}])->toArray(),
            );
        }

        $this->syncAvailability();
        $this->syncAssociations();
        $this->syncCollections();
    }

    /**
     * Delete the product.
     *
     * @return void
     */
    public function delete()
    {
        $this->product->delete();
        $this->notify(
            __('adminhub::notifications.products.deleted'),
            'hub.products.index'
        );
    }

    /**
     * Restore the product.
     *
     * @return void
     */
    public function restore()
    {
        $this->product->restore();
        $this->showRestoreConfirm = false;
        $this->notify(
            __('adminhub::notifications.products.product_restored')
        );
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // dd($this->attributeData);
        return view('adminhub::livewire.components.products.show')->layout('adminhub::layouts.base');
    }

    protected function getSlotContexts()
    {
        return ['product.all', 'product.show'];
    }
}
