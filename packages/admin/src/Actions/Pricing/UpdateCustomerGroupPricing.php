<?php

namespace Lunar\Hub\Actions\Pricing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class UpdateCustomerGroupPricing
{
    /**
     * Execute the action.
     *
     * @return \Illuminate\Support\Collection
     */
    public function execute(Model $owner, Collection $groups)
    {
        $groupsArray = $groups->toArray();

        foreach ($groupsArray as $groupId => $prices) {
            $groupsArray[$groupId] = app(UpdatePrices::class)->execute(
                $owner,
                collect($prices)
            );
        }

        return collect($groupsArray);
    }
}
