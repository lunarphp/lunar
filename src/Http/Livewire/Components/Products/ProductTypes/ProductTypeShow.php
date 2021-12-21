<?php

namespace GetCandy\Hub\Http\Livewire\Components\Products\ProductTypes;

use GetCandy\Models\Attribute;
use GetCandy\Models\ProductType;
use Illuminate\Support\Facades\DB;

class ProductTypeShow extends AbstractProductType
{
    /**
     * Attributes which are ready to be synced.
     *
     * @var array
     */
    public array $attributes = [];

    public bool $deleteDialogVisible = false;

    public function mount()
    {
        $systemAttributes = Attribute::system(ProductType::class)->get();
        $this->selectedAttributes = $this->productType->mappedAttributes->merge($systemAttributes);
    }

    /**
     * Register the validation rules.
     *
     * @return void
     */
    protected function rules()
    {
        return [
            'productType.name' => [
                'required',
                'string',
                'unique:'.$this->productType->getTable().',name,'.$this->productType->id,
            ],
        ];
    }

    /**
     * Method to handle product type saving.
     *
     * @return void
     */
    public function update()
    {
        $this->validate();

        $this->productType->save();

        $this->productType->mappedAttributes()->sync(
            $this->selectedAttributes->pluck('id')
        );

        $this->notify(
            __('adminhub::catalogue.product-types.show.updated_message'),
            'hub.product-types.index'
        );
    }

    public function getCanDeleteProperty()
    {
        return ! $this->productType->products()->count();
    }

    /**
     * Delete the variant.
     *
     * @return void
     */
    public function delete()
    {
        if (! $this->canDelete) {
            $this->notify(
                __('adminhub::catalogue.product-types.show.delete.disabled_message')
            );
            $this->deleteDialogVisible = false;

            return;
        }

        DB::transaction(fn () => $this->productType->delete());

        $this->notify(
            __('adminhub::catalogue.product-types.show.delete.delete_notification'),
            'hub.product-types.index'
        );
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.products.product-types.show')
            ->layout('adminhub::layouts.base');
    }
}
