<?php

namespace Lunar\Hub\Actions\Pricing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Lunar\Models\Currency;
use Lunar\Models\Price;

class UpdatePrices
{
    /**
     * Execute the action.
     *
     * @param  Model  $owner
     * @param  \Illuminate\Support\Collection  $prices
     * @return \Illuminate\Support\Collection
     */
    public function execute(Model $owner, Collection $prices)
    {
        $pricesArray = $prices->toArray();

        foreach ($pricesArray as $key => $price) {
            $result = $this->updateOrCreatePrice(
                $owner,
                $price['tier'] ?? 1,
                $price['currency_id'],
                $price['price'],
                ! empty($price['compare_price']) ? $price['compare_price'] : null,
                $price['customer_group_id'] ?? null,
                $price['id'] ?? null
            );
            $pricesArray[$key]['id'] = $result->id;
        }

        return collect($pricesArray);
    }

    /**
     * Create or update a price.
     *
     * @param  Model  $owner
     * @param  int  $tier
     * @param  int|null  $groupId
     * @param  float  $price
     * @param  int  $currencyId
     * @param  int|null  $id
     * @return \Lunar\Models\Price
     */
    private function updateOrCreatePrice(Model $owner, $tier, $currencyId, $price, $comparePrice = null, $groupId = null, $id = null)
    {
        $priceModel = $id ? Price::find($id) : new Price();

        $currency = Currency::find($currencyId);

        $priceModel->fill([
            'price'             => (int) ($price * $currency->factor),
            'compare_price'     => $comparePrice ? (int) ($comparePrice * $currency->factor) : null,
            'currency_id'       => $currencyId,
            'customer_group_id' => $groupId,
            'tier'              => $tier,
            'priceable_id'      => $owner->id,
            'priceable_type'    => get_class($owner),
        ]);

        $priceModel->save();

        return $priceModel;
    }
}
