<?php

namespace Lunar\SearchRelevance\Listeners;

use Lunar\Core\Events\Customers\CustomerDeleted;
use Lunar\SearchRelevance\Models\SearchQuery;

/**
 * Removes a deleted customer's id from their logged searches. The searches
 * stay, since what they clicked and bought is the store's learning, but the
 * only link back to the person goes: sessions are stored as a hash or a cart id.
 */
class UnlinkDeletedCustomer
{
    public function handle(CustomerDeleted $event): void
    {
        SearchQuery::query()
            ->where('customer_id', $event->customer->getKey())
            ->update(['customer_id' => null]);
    }
}
