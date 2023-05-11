<?php

namespace Lunar\Hub\Actions\Pricing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Lunar\Models\Currency;
use Lunar\Models\Price;

class UpdateTieredPricing
{
    /**
     * Execute the action.
     *
     * @return \Illuminate\Support\Collection
     */
    public function execute(Model $owner, Collection $tieredPrices)
    {
        $tiers = $tieredPrices->toArray();

        DB::transaction(function () use ($owner, &$tiers) {
            $pricesToKeep = [];

            foreach ($tiers as $tierKey => $priceTier) {
                $tierLimit = $priceTier['tier'];
                $groupId = $priceTier['customer_group_id'] == '*' ? null : $priceTier['customer_group_id'];

                foreach ($priceTier['prices'] as $priceKey => $price) {
                    $price = $this->createOrUpdatePrice(
                        $owner,
                        $tierLimit,
                        $price['price'],
                        $price['currency_id'],
                        $groupId,
                        $price['id'] ?? null
                    );
                    $tiers[$tierKey]['prices'][$priceKey]['id'] = $price->id;
                    $pricesToKeep[] = $price->id;
                }
            }

            $owner->prices()->whereNotIn('id', $pricesToKeep)->where('tier', '>', 1)->delete();
        });

        return collect($tiers)->sortBy('tier')->values();
    }

    /**
     * Create or update a price.
     *
     * @param  int  $tier
     * @param  int|null  $groupId
     * @param  float  $price
     * @param  int  $currencyId
     * @param  int|null  $id
     * @return \Lunar\Models\Price
     */
    private function createOrUpdatePrice(Model $owner, $tier, $price, $currencyId, $groupId = null, $id = null)
    {
        $priceModel = $id ? Price::find($id) : new Price();

        // If the decimals weren't provided we need to add them in.
        $currency = Currency::find($currencyId);

        $priceModel->fill([
            'price' => (int) bcmul($price, $currency->factor),
            'currency_id' => $currencyId,
            'customer_group_id' => $groupId,
            'tier' => $tier,
            'priceable_id' => $owner->id,
            'priceable_type' => $owner->getMorphClass(),
        ]);

        $priceModel->save();

        return $priceModel;
    }
}
