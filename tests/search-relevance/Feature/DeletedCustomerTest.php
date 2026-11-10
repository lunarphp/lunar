<?php

use Lunar\Core\Models\Customer;
use Lunar\SearchRelevance\Models\SearchQuery;
use Lunar\Tests\SearchRelevance\Support\Fixtures;
use Lunar\Tests\SearchRelevance\TestCase;

uses(TestCase::class)->group('search-relevance');

it('unlinks a deleted customer from their logged searches', function () {
    Fixtures::storefront();

    $customer = Customer::factory()->create();
    $theirs = SearchQuery::factory()->create(['customer_id' => $customer->id]);
    $other = SearchQuery::factory()->create(['customer_id' => Customer::factory()->create()->id]);

    $customer->delete();

    // The search stays for learning, but no longer points at the customer.
    expect($theirs->fresh()->customer_id)->toBeNull()
        ->and($other->fresh()->customer_id)->not->toBeNull();
});
