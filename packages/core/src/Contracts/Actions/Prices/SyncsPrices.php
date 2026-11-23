<?php

namespace Lunar\Core\Contracts\Actions\Prices;

use Illuminate\Database\Eloquent\Model;

interface SyncsPrices
{
    /**
     * Make the priceable's price rows exactly the given set. A row is
     * identified by its currency, customer group and minimum quantity.
     *
     * @param  array<int, array{currency_id: int, customer_group_id: ?int, min_quantity: int, price: int, list_price?: ?int}>  $prices
     */
    public function execute(Model $priceable, array $prices): Model;
}
