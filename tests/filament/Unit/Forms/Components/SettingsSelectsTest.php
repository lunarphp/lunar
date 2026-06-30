<?php

use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\TaxClass;
use Lunar\Core\Models\TaxZone;
use Lunar\Filament\Forms\Components\ChannelSelect;
use Lunar\Filament\Forms\Components\CountrySelect;
use Lunar\Filament\Forms\Components\CurrencySelect;
use Lunar\Filament\Forms\Components\LanguageSelect;
use Lunar\Filament\Forms\Components\StateSelect;
use Lunar\Filament\Forms\Components\TaxClassSelect;
use Lunar\Filament\Forms\Components\TaxZoneSelect;
use Lunar\Tests\Filament\TestCase;

uses(TestCase::class);

it('instantiates CurrencySelect against the currency relationship', function () {
    $component = CurrencySelect::make('currency_id');

    expect($component)->toBeInstanceOf(CurrencySelect::class)
        ->and($component->lunarModel())->toBe(Currency::class)
        ->and($component->getRelationshipName())->toBe('currency')
        ->and($component->isPreloaded())->toBeTrue();
});

it('instantiates ChannelSelect against the channel relationship', function () {
    $component = ChannelSelect::make('channel_id');

    expect($component)->toBeInstanceOf(ChannelSelect::class)
        ->and($component->lunarModel())->toBe(Channel::class)
        ->and($component->getRelationshipName())->toBe('channel');
});

it('instantiates LanguageSelect against the language relationship', function () {
    $component = LanguageSelect::make('language_id');

    expect($component)->toBeInstanceOf(LanguageSelect::class)
        ->and($component->lunarModel())->toBe(Language::class);
});

it('instantiates TaxClassSelect against the taxClass relationship', function () {
    $component = TaxClassSelect::make('tax_class_id');

    expect($component)->toBeInstanceOf(TaxClassSelect::class)
        ->and($component->lunarModel())->toBe(TaxClass::class);
});

it('instantiates TaxZoneSelect against the taxZone relationship', function () {
    $component = TaxZoneSelect::make('tax_zone_id');

    expect($component)->toBeInstanceOf(TaxZoneSelect::class)
        ->and($component->lunarModel())->toBe(TaxZone::class);
});

it('instantiates CountrySelect tied to the Country model', function () {
    $component = CountrySelect::make('country_id');

    expect($component)->toBeInstanceOf(CountrySelect::class)
        ->and($component->lunarModel())->toBe(Country::class)
        ->and($component->getRelationshipName())->toBeNull();
});

it('switches CountrySelect to iso3 mode', function () {
    $component = CountrySelect::make('country_iso')->iso3();

    expect($component)->toBeInstanceOf(CountrySelect::class);
});

it('instantiates StateSelect with a country-dependent datalist', function () {
    $component = StateSelect::make('state')->dependsOn('country_id');

    expect($component)->toBeInstanceOf(StateSelect::class)
        ->and($component->getName())->toBe('state');
});
