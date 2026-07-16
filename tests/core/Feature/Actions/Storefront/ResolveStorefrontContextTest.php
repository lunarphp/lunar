<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Contracts\Actions\Storefront\ResolvesStorefrontContext;
use Lunar\Core\DataObjects\StorefrontContext;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Region;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

function resolveContext(): ResolvesStorefrontContext
{
    return app(ResolvesStorefrontContext::class);
}

test('it resolves the configured defaults when nothing is supplied', function () {
    $channel = Channel::factory()->create(['default' => true]);
    $currency = Currency::factory()->create(['default' => true]);
    $language = Language::factory()->create(['default' => true]);
    $group = CustomerGroup::factory()->create(['default' => true]);

    $context = resolveContext()->execute();

    expect($context)->toBeInstanceOf(StorefrontContext::class);
    expect($context->channel->id)->toBe($channel->id);
    expect($context->currency->id)->toBe($currency->id);
    expect($context->language->id)->toBe($language->id);
    expect($context->customer)->toBeNull();
    expect($context->customerGroups->pluck('id')->all())->toBe([$group->id]);
});

test('explicit overrides win over the defaults', function () {
    Channel::factory()->create(['default' => true]);
    Currency::factory()->create(['default' => true, 'code' => 'GBP']);
    Language::factory()->create(['default' => true]);
    CustomerGroup::factory()->create(['default' => true]);

    $otherCurrency = Currency::factory()->create(['default' => false, 'code' => 'EUR']);
    $otherLanguage = Language::factory()->create(['default' => false, 'code' => 'fr']);

    $context = resolveContext()->execute(
        currency: $otherCurrency,
        language: $otherLanguage,
    );

    expect($context->currency->code)->toBe('EUR');
    expect($context->language->code)->toBe('fr');
});

test('customer groups derive from the customer when present', function () {
    Channel::factory()->create(['default' => true]);
    Currency::factory()->create(['default' => true]);
    CustomerGroup::factory()->create(['default' => true]);

    $trade = CustomerGroup::factory()->create(['default' => false, 'handle' => 'trade']);
    $customer = Customer::factory()->create();
    $customer->customerGroups()->attach($trade);

    $context = resolveContext()->execute(customer: $customer);

    expect($context->customer->id)->toBe($customer->id);
    expect($context->customerGroups->pluck('id')->all())->toBe([$trade->id]);
});

test('language is null when no default language is configured', function () {
    $channel = Channel::factory()->create(['default' => true]);
    $currency = Currency::factory()->create(['default' => true]);

    $context = resolveContext()->execute();

    expect($context->language)->toBeNull();
    expect($context->channel->id)->toBe($channel->id);
    expect($context->currency->id)->toBe($currency->id);
});

test('channel, currency and language default from the resolved region', function () {
    // globals, distinct from the region's own selections
    Channel::factory()->create(['default' => true]);
    Currency::factory()->create(['default' => true, 'code' => 'GBP']);
    Language::factory()->create(['default' => true]);
    CustomerGroup::factory()->create(['default' => true]);

    $regionChannel = Channel::factory()->create(['default' => false]);
    $regionCurrency = Currency::factory()->create(['default' => false, 'code' => 'EUR']);
    $regionLanguage = Language::factory()->create(['default' => false, 'code' => 'fr']);

    $region = Region::factory()->create([
        'default' => true,
        'channel_id' => $regionChannel->id,
        'currency_id' => $regionCurrency->id,
        'language_id' => $regionLanguage->id,
    ]);

    $context = resolveContext()->execute();

    expect($context->region->id)->toBe($region->id);
    expect($context->channel->id)->toBe($regionChannel->id);
    expect($context->currency->code)->toBe('EUR');
    expect($context->language->code)->toBe('fr');
});

test('an explicit override beats the region default', function () {
    Channel::factory()->create(['default' => true]);
    Currency::factory()->create(['default' => true, 'code' => 'GBP']);
    CustomerGroup::factory()->create(['default' => true]);

    $regionCurrency = Currency::factory()->create(['default' => false, 'code' => 'EUR']);
    Region::factory()->create([
        'default' => true,
        'channel_id' => Channel::getDefault()->id,
        'currency_id' => $regionCurrency->id,
        'language_id' => Language::factory()->create(['default' => true])->id,
    ]);

    $override = Currency::factory()->create(['default' => false, 'code' => 'USD']);

    $context = resolveContext()->execute(currency: $override);

    expect($context->currency->code)->toBe('USD');
});

test('an explicit region overrides the default region', function () {
    Channel::factory()->create(['default' => true]);
    Currency::factory()->create(['default' => true]);
    CustomerGroup::factory()->create(['default' => true]);
    $language = Language::factory()->create(['default' => true]);

    Region::factory()->create([
        'default' => true,
        'channel_id' => Channel::getDefault()->id,
        'currency_id' => Currency::getDefault()->id,
        'language_id' => $language->id,
    ]);

    $other = Region::factory()->create([
        'default' => false,
        'channel_id' => Channel::getDefault()->id,
        'currency_id' => Currency::getDefault()->id,
        'language_id' => $language->id,
    ]);

    $context = resolveContext()->execute(region: $other);

    expect($context->region->id)->toBe($other->id);
});
