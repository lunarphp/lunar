<?php

namespace Lunar\DemoData\Generators;

use Illuminate\Support\Collection;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\TaxClass;
use Lunar\Core\Models\TaxRate;
use Lunar\Core\Models\TaxRateAmount;
use Lunar\Core\Models\TaxZone;
use Lunar\DemoData\Support\DemoContext;

/**
 * Establishes the store's foundation: language, channel, customer group,
 * default location, currencies, and a tax class/zone/rate. Keyed on natural
 * handles/codes so it reuses anything `lunar:install` already created and is
 * safe to re-run.
 */
class FoundationGenerator implements Generator
{
    /**
     * Display name and exchange rate (relative to the default) for the
     * currencies the demo knows about. The configured default is forced to 1.0.
     *
     * @var array<string, array{name: string, rate: float}>
     */
    protected array $currencyMeta = [
        'USD' => ['name' => 'US Dollar', 'rate' => 1.0],
        'GBP' => ['name' => 'British Pound', 'rate' => 0.79],
        'EUR' => ['name' => 'Euro', 'rate' => 0.92],
    ];

    public function generate(DemoContext $context): void
    {
        $context->set('language', $this->language());
        $context->set('channel', $this->channel());
        $context->set('customerGroup', $this->customerGroup());
        $context->set('location', $this->location());

        $currencies = $this->currencies();
        $context->set('currencies', $currencies);
        $context->set('currency', $currencies->firstWhere('default', true));

        $taxClass = $this->taxClass();
        $context->set('taxClass', $taxClass);
        $context->set('taxZone', $this->taxZone($taxClass));
    }

    protected function language(): Language
    {
        return Language::query()->firstOrCreate(
            ['code' => 'en'],
            ['name' => 'English', 'default' => true],
        );
    }

    protected function channel(): Channel
    {
        return Channel::query()->firstOrCreate(
            ['handle' => 'webstore'],
            ['name' => 'Webstore', 'default' => true, 'url' => 'https://demo-store.lunarphp.io'],
        );
    }

    protected function customerGroup(): CustomerGroup
    {
        return CustomerGroup::query()->firstOrCreate(
            ['handle' => 'retail'],
            ['name' => 'Retail', 'default' => true],
        );
    }

    protected function location(): Location
    {
        return Location::query()->firstOrCreate(
            ['handle' => 'default'],
            ['name' => 'Default', 'default' => true],
        );
    }

    /**
     * @return Collection<int, Currency>
     */
    protected function currencies(): Collection
    {
        $codes = (array) config('lunar.demo-data.currencies', ['USD']);
        $defaultCode = $codes[0] ?? 'USD';

        $currencies = collect($codes)->map(function (string $code) use ($defaultCode) {
            $meta = $this->currencyMeta[$code] ?? ['name' => $code, 'rate' => 1.0];
            $isDefault = $code === $defaultCode;

            return Currency::query()->updateOrCreate(
                ['code' => $code],
                [
                    'name' => $meta['name'],
                    'exchange_rate' => $isDefault ? 1.0 : $meta['rate'],
                    'decimal_places' => 2,
                    'enabled' => true,
                    'default' => $isDefault,
                ],
            );
        });

        // Guarantee a single default even if an unrelated currency held the flag.
        Currency::query()->where('code', '!=', $defaultCode)->update(['default' => false]);

        return $currencies;
    }

    protected function taxClass(): TaxClass
    {
        return TaxClass::query()->firstOrCreate(
            ['name' => 'Default Tax Class'],
            ['default' => true],
        );
    }

    protected function taxZone(TaxClass $taxClass): TaxZone
    {
        $zone = TaxZone::query()->firstOrCreate(
            ['name' => 'Default Tax Zone'],
            [
                'zone_type' => 'country',
                'default' => true,
                'active' => true,
            ],
        );

        $rate = TaxRate::query()->firstOrCreate(
            ['tax_zone_id' => $zone->id, 'name' => 'VAT'],
            ['priority' => 1],
        );

        TaxRateAmount::query()->firstOrCreate(
            ['tax_rate_id' => $rate->id, 'tax_class_id' => $taxClass->id],
            ['percentage' => 20],
        );

        // Mirror lunar:install — apply the zone to every known country so tax resolves.
        if (! $zone->countries()->exists() && Country::query()->exists()) {
            $zone->countries()->createMany(
                Country::query()->pluck('id')->map(fn ($id) => ['country_id' => $id])->all()
            );
        }

        return $zone;
    }
}
