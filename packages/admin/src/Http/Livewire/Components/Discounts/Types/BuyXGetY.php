<?php

namespace Lunar\Hub\Http\Livewire\Components\Discounts\Types;

use Illuminate\Support\Collection;
use Lunar\Facades\DB;
use Lunar\Models\Currency;
use Lunar\Models\Discount;
use Lunar\Models\Product;

class BuyXGetY extends AbstractDiscountType
{
    /**
     * The instance of the discount.
     */
    public Discount $discount;

    /**
     * The product discount conditions
     */
    public Collection $conditions;

    /**
     * The product discount rewards
     */
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
            'discount.data.max_reward_qty' => 'required|numeric',
            'selectedConditions' => 'array|min:1',
            'selectedRewards' => 'array|min:1',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function mount()
    {
        parent::mount();

        $this->conditions = $this->discount->purchasableConditions()
            ->wherePurchasableType(Product::class)
            ->pluck('purchasable_id')->values();

        $this->rewards = $this->discount->purchasableRewards()
            ->wherePurchasableType(Product::class)
            ->pluck('purchasable_id')->values();
    }

    /**
     * Return the purchasable condition models.
     *
     * @return Collection
     */
    public function getPurchasableConditionsProperty()
    {
        return Product::whereIn(
            'id',
            $this->conditions
        )->get();
    }

    /**
     * Return the purchasable reward models.
     *
     * @return Collection
     */
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

    /**
     * Remove a condition based on the product id
     *
     * @param  string|int  $productId
     * @return void
     */
    public function removeCondition($productId)
    {
        $index = $this->conditions->search($productId);

        $conditions = $this->conditions;

        $conditions->forget($index);

        $this->conditions = $conditions;

        $this->emit('discount.conditions', $conditions->toArray());
    }

    /**
     * Remove a reward based on the product id
     *
     * @param  string|int  $productId
     * @return void
     */
    public function removeReward($productId)
    {
        $index = $this->rewards->search($productId);

        $rewards = $this->rewards;

        $rewards->forget($index);

        $this->rewards = $rewards;
    }

    /**
     * Select products
     *
     * @param  string|null  $ref
     * @return void
     */
    public function selectProducts(array $ids, $ref = null)
    {
        if ($ref == 'discount-conditions') {
            $this->conditions = collect($ids);
            $this->emit('discount.conditions', $this->conditions->toArray());
        }

        if ($ref == 'discount-rewards') {
            $this->rewards = collect($ids);
            $this->emit('discount.rewards', $this->rewards->toArray());
        }
    }

    public function getValidationMessages()
    {
        return [
            'discount.data.min_qty.required' => 'This field is required',
            'discount.data.reward_qty.required' => 'This field is required',
            'discount.data.max_reward_qty.required' => 'This field is required',
        ];
    }

    /**
     * Save the product discount.
     *
     * @return void
     */
    public function save($discountId)
    {
        $this->discount = Discount::find($discountId);

        DB::transaction(function () {
            $conditions = $this->conditions;

            $this->discount->purchasableConditions()
                ->whereNotIn('purchasable_id', $conditions)
                ->delete();

            foreach ($conditions as $condition) {
                $this->discount->purchasables()->firstOrCreate([
                    'discount_id' => $this->discount->id,
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
                    'discount_id' => $this->discount->id,
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
        return view('adminhub::livewire.components.discounts.types.buy-x-get-y')
            ->layout('adminhub::layouts.base');
    }
}
