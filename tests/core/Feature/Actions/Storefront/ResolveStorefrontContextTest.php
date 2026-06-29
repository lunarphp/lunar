<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Contracts\Actions\Storefront\ResolvesStorefrontContext;
use Lunar\Core\DataObjects\StorefrontContext;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Language;
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
    Currency::factory()->create(['default' => true]);
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
