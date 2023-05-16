<?php

namespace Lunar\Hub\Http\Livewire\Components\Discounts;

use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Lunar\Models\Discount;

class DiscountShow extends AbstractDiscount
{
    /**
     * The instance of the discount.
     *
     * @var Discount
     */
    public Discount $discount;

    /**
     * The confirmation text to delete the discount.
     *
     * @var string|null
     */
    public ?string $deleteConfirm = null;

    /**
     * {@inheritDoc}.
     */
    public function rules()
    {
        $rules = array_merge([
            'discount.name' => 'required|unique:'.Discount::class.',name,'.$this->discount->id,
            'discount.handle' => 'required|unique:'.Discount::class.',handle,'.$this->discount->id,
            'discount.stop' => 'nullable',
            'discount.max_uses' => 'nullable|numeric|min:0',
            'discount.max_uses_per_user' => 'nullable|numeric|min:0',
            'discount.priority' => 'required|min:1',
            'discount.starts_at' => 'required|date',
            'discount.coupon' => 'nullable',
            'discount.ends_at' => 'nullable|date|after:discount.starts_at',
            'discount.type' => 'string|required',
            'discount.data' => 'array',
            'selectedCollections' => 'array',
            'selectedBrands' => 'array',
        ], $this->getDiscountComponent()->rules());

        foreach ($this->currencies as $currency) {
            $rules['discount.data.min_prices.'.$currency->code] = 'nullable';
        }

        return $rules;
    }

    /**
     * Computed property to determine whether the discount can be deleted.
     *
     * @return bool
     */
    public function getCanDeleteProperty()
    {
        return $this->deleteConfirm === trim($this->discount->name);
    }

    /**
     * Delete the discount.
     *
     * @return Redirector
     */
    public function delete()
    {
        DB::transaction(function () {
            $this->discount->purchasables()->delete();
            $this->discount->purchasableConditions()->delete();
            $this->discount->purchasableRewards()->delete();
            $this->discount->collections()->delete();
            $this->discount->customerGroups()->detach();
            $this->discount->channels()->detach();
            $this->discount->users()->detach();
            $this->discount->delete();
        });

        $this->notify(
            __('adminhub::notifications.discount.deleted'),
            'hub.discounts.index'
        );
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.discounts.show')
            ->layout('adminhub::layouts.app');
    }
}
