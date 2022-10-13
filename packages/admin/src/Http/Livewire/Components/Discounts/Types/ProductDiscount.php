<?php

namespace Lunar\Hub\Http\Livewire\Components\Discounts\Types;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Lunar\Models\Currency;
use Lunar\Models\Discount;
use Lunar\Models\Product;

class ProductDiscount extends AbstractDiscountType
{
    /**
     * The instance of the discount.
     *
     * @var Discount
     */
    public Discount $discount;

    public Collection $conditions;

    public Collection $rewards;

    /**
     * {@ineheritDoc}.
     */
    public function rules()
    {
        return [
            'discount.data' => 'array',
            'discount.data.min_qty' => 'required',
            'discount.data.reward_qty' => 'required|numeric',
        ];
    }

    public function mount()
    {
        $this->conditions = $this->discount->purchasableConditions()
            ->wherePurchasableType(Product::class)
            ->pluck('purchasable_id')->values();

        $this->rewards = $this->discount->purchasableRewards()
            ->wherePurchasableType(Product::class)
            ->pluck('purchasable_id')->values();

//         $this->conditions = $this->purchasableConditions->pluck('id')->unique()->values();
//
//         $this->rewards = $this->purchasableRewards->pluck('id')->unique()->values();
    }

    public function getPurchasableConditionsProperty()
    {
        return Product::whereIn(
            'id',
            $this->conditions
        )->get();
    }

    public function getPurchasableRewardsProperty()
    {
        return Product::whereIn(
            'id',
            $this->rewards
        )->get();
    }

    /**
     * Handle when the discount data is updated.
     *
     * @return void
     */
    public function updatedDiscountDataMinQty()
    {
        $this->emitUp('discountData.updated', $this->discount->data);
    }

    /**
     * Handle when the discount data is updated.
     *
     * @return void
     */
    public function updatedDiscountDataRewardQty()
    {
        $this->emitUp('discountData.updated', $this->discount->data);
    }

    public function removeCondition($productId)
    {
        $index = $this->conditions->search($productId);

        $conditions = $this->conditions;

        $conditions->forget($index);

        $this->conditions = $conditions;
    }

    public function removeReward($productId)
    {
        $index = $this->rewards->search($productId);

        $rewards = $this->rewards;

        $rewards->forget($index);

        $this->rewards = $rewards;
    }

    public function selectProducts($ids, $ref = null)
    {
        if ($ref == 'discount-conditions') {
            $this->conditions = collect($ids);
        }

        if ($ref == 'discount-rewards') {
            $this->rewards = collect($ids);
        }
    }

    public function save()
    {
        DB::transaction(function () {
            $conditions = $this->conditions;

            $this->discount->purchasableConditions()
                ->whereNotIn('purchasable_id', $conditions)
                ->delete();

            foreach ($conditions as $condition) {
                $this->discount->purchasables()->firstOrCreate([
                    'type' => 'condition',
                    'purchasable_type' => Product::class,
                    'purchasable_id' => $condition,
                ]);
            }

            $rewards = $this->rewards;

            $this->discount->purchasableConditions()
                ->whereNotIn('purchasable_id', $conditions)
                ->delete();

            foreach ($rewards as $reward) {
                $this->discount->purchasables()->firstOrCreate([
                    'type' => 'reward',
                    'purchasable_type' => Product::class,
                    'purchasable_id' => $reward,
                ]);
            }
        });
    }

    /**
     * Return the available currencies.
     *
     * @return Collection
     */
    public function getCurrenciesProperty()
    {
        return Currency::get();
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.discounts.types.product-discount')
            ->layout('adminhub::layouts.base');
    }
}
