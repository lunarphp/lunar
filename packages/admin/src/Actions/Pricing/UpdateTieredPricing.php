<?php

namespace GetCandy\Hub\Actions\Pricing;

use GetCandy\Models\Currency;
use GetCandy\Models\Price;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UpdateTieredPricing
{
    /**
     * Execute the action.
     *
     * @param  Model  $owner
     * @param  Collection  $tieredPrices
     * @return void
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
     * @param  Model  $owner
     * @param  int  $tier
     * @param  int|null  $groupId
     * @param  float  $price
     * @param  int  $currencyId
     * @param  int|null  $id
     * @return \GetCandy\Models\Price
     */
    private function createOrUpdatePrice(Model $owner, $tier, $price, $currencyId, $groupId = null, $id = null)
    {
        $priceModel = $id ? Price::find($id) : new Price();

        // If the decimals weren't provided we need to add them in.
        $currency = Currency::find($currencyId);

        $priceModel->fill([
            'price'             => (int) ($price * $currency->factor),
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
