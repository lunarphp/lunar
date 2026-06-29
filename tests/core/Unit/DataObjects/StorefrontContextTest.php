<?php

use Illuminate\Support\Collection;
use Lunar\Core\DataObjects\StorefrontContext;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Region;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

function contextCurrency(string $code = 'GBP', int $id = 1): Currency
{
    $currency = new Currency;
    $currency->id = $id;
    $currency->code = $code;
    $currency->exists = true;

    return $currency;
}

function contextLanguage(string $code = 'en', int $id = 1): Language
{
    $language = new Language;
    $language->id = $id;
    $language->code = $code;
    $language->exists = true;

    return $language;
}

function storefrontContext(?Customer $customer = null, ?Collection $customerGroups = null): StorefrontContext
{
    $channel = new Channel;
    $channel->id = 1;
    $channel->name = 'Webstore';
    $channel->handle = 'webstore';
    $channel->exists = true;

    $group = new CustomerGroup;
    $group->id = 1;
    $group->handle = 'retail';
    $group->exists = true;

    return new StorefrontContext(
        channel: $channel,
        currency: contextCurrency(),
        language: contextLanguage(),
        customer: $customer,
        customerGroups: $customerGroups ?? collect([$group]),
    );
}

test('it carries the resolved selections', function () {
    $context = storefrontContext();

    expect($context->channel->handle)->toBe('webstore');
    expect($context->currency->code)->toBe('GBP');
    expect($context->language->code)->toBe('en');
    expect($context->customer)->toBeNull();
    expect($context->customerGroups)->toHaveCount(1);
});

test('withCurrency returns a new instance leaving the original untouched', function () {
    $context = storefrontContext();

    $next = $context->withCurrency(contextCurrency('EUR', 2));

    expect($next)->not->toBe($context);
    expect($next->currency->code)->toBe('EUR');
    expect($context->currency->code)->toBe('GBP');
    expect($next->channel)->toBe($context->channel);
    expect($next->language)->toBe($context->language);
});

test('withCustomer swaps the customer without touching groups', function () {
    $context = storefrontContext();

    $customer = new Customer;
    $customer->id = 99;
    $customer->exists = true;

    $next = $context->withCustomer($customer);

    expect($next->customer->id)->toBe(99);
    expect($context->customer)->toBeNull();
    expect($next->customerGroups)->toBe($context->customerGroups);
});

test('the with* chain composes', function () {
    $context = storefrontContext()
        ->withCurrency(contextCurrency('EUR', 2))
        ->withLanguage(contextLanguage('fr', 2));

    expect($context->currency->code)->toBe('EUR');
    expect($context->language->code)->toBe('fr');
});

test('region defaults to null and withRegion returns a new instance carrying it', function () {
    $context = storefrontContext();
    expect($context->region)->toBeNull();

    $region = new Region;
    $region->id = 1;
    $region->handle = 'uk';
    $region->exists = true;

    $next = $context->withRegion($region);

    expect($next->region->id)->toBe(1);
    expect($context->region)->toBeNull();
    expect($next->channel)->toBe($context->channel);
});

test('displaysPricesIncludingTax reads the region preference, else the global default', function () {
    config()->set('lunar.pricing.stored_inclusive_of_tax', false);

    $region = new Region;
    $region->prices_inc_tax = true;
    $region->exists = true;

    expect(storefrontContext()->withRegion($region)->displaysPricesIncludingTax())->toBeTrue();

    // no region falls back to the global default
    expect(storefrontContext()->displaysPricesIncludingTax())->toBeFalse();
});
