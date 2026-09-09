<?php

namespace Lunar\Shipping\Panel\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\State;
use Lunar\Shipping\Contracts\Actions\ShippingZones\DeletesShippingZone;
use Lunar\Shipping\Contracts\Actions\ShippingZones\UpdatesShippingZone;
use Lunar\Shipping\Models\ShippingExclusionList;
use Lunar\Shipping\Models\ShippingMethod;
use Lunar\Shipping\Models\ShippingZone;
use Lunar\Shipping\Panel\Http\Requests\ZoneRequest;
use Lunar\Shipping\Panel\Support\CurrencyOptions;
use Lunar\Shipping\Panel\Support\RateRows;

class ZoneEditController
{
    public function __construct(
        protected RateRows $rateRows,
        protected CurrencyOptions $currencyOptions,
    ) {}

    public function edit(ShippingZone $shippingZone): Response
    {
        return Inertia::render('shipping::settings/shipping/zones/Edit', [
            'zone' => [
                'id' => $shippingZone->id,
                'name' => $shippingZone->name,
                'type' => $shippingZone->type,
            ],
            'coverage' => [
                'countries' => $shippingZone->countries()->pluck('country_id'),
                'states' => $shippingZone->states()->pluck('state_id'),
                'postcodes' => $shippingZone->postcodes()->orderBy('postcode')->pluck('postcode'),
            ],
            'exclusionLists' => $shippingZone->shippingExclusions()->get()->pluck('id'),
            'rates' => $this->rateRows->forZone($shippingZone),
            'methods' => ShippingMethod::query()->orderBy('name')->get()->map(fn (ShippingMethod $method) => [
                'id' => $method->id,
                'name' => $method->name,
                'charge_by' => $method->data['charge_by'] ?? 'cart_total',
                'weight_unit' => $method->weight_unit ?: 'kg',
            ]),
            'currencies' => $this->currencyOptions->all(),
            'customerGroups' => CustomerGroup::query()->orderBy('name')->get(['id', 'name']),
            'allExclusionLists' => ShippingExclusionList::query()->orderBy('name')->get(['id', 'name']),
            'countries' => Country::query()->orderBy('name')->get(['id', 'name', 'iso2']),
            'states' => State::query()->orderBy('name')->get(['id', 'name', 'code', 'country_id']),
            'pricesIncludeTax' => (bool) config('lunar.pricing.stored_inclusive_of_tax', false),
            'urls' => [
                'update' => route('panel.settings.shipping.zones.update', $shippingZone),
                'destroy' => route('panel.settings.shipping.zones.destroy', $shippingZone),
                'index' => route('panel.settings.shipping.zones.index'),
                'ratesStore' => route('panel.settings.shipping.zones.rates.store', $shippingZone),
            ],
        ]);
    }

    public function update(ZoneRequest $request, ShippingZone $shippingZone, UpdatesShippingZone $updatesShippingZone): RedirectResponse
    {
        $updatesShippingZone->execute($shippingZone, $request->zoneAttributes());

        return back()->with('success', __('shipping::zones.flash_updated'));
    }

    public function destroy(ShippingZone $shippingZone, DeletesShippingZone $deletesShippingZone): RedirectResponse
    {
        $deletesShippingZone->execute($shippingZone);

        return redirect()
            ->route('panel.settings.shipping.zones.index')
            ->with('success', __('shipping::zones.flash_deleted'));
    }
}
