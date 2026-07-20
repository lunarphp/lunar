<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Contracts\Actions\TaxZones\DeletesTaxZone;
use Lunar\Core\Contracts\Actions\TaxZones\UpdatesTaxZone;
use Lunar\Core\Exceptions\TaxZoneActionException;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\State;
use Lunar\Core\Models\TaxClass;
use Lunar\Core\Models\TaxRate;
use Lunar\Core\Models\TaxZone;
use Lunar\Panel\Http\Requests\Settings\TaxZoneRequest;

class TaxZoneEditController
{
    public function edit(TaxZone $taxZone): Response
    {
        return Inertia::render('settings/tax-zones/Edit', [
            'taxZone' => [
                'id' => $taxZone->id,
                'name' => $taxZone->name,
                'zone_type' => $taxZone->zone_type,
                'active' => $taxZone->active,
                'default' => $taxZone->default,
            ],
            'coverage' => [
                'countries' => $taxZone->countries()->pluck('country_id'),
                'states' => $taxZone->states()->pluck('state_id'),
                'postcodes' => $taxZone->postcodes()->get()->map(fn ($row) => [
                    'country_id' => $row->country_id,
                    'postcode' => $row->postcode,
                ]),
                'customerGroups' => $taxZone->customerGroups()->pluck('customer_group_id'),
            ],
            'rates' => $taxZone->taxRates()
                ->with('taxRateAmounts:id,tax_rate_id,tax_class_id,percentage')
                ->orderBy('priority')
                ->get()
                ->map(fn (TaxRate $rate) => [
                    'id' => $rate->id,
                    'name' => $rate->name,
                    'priority' => $rate->priority,
                    'amounts' => $rate->taxRateAmounts->mapWithKeys(fn ($amount) => [
                        $amount->tax_class_id => (float) $amount->percentage,
                    ]),
                ]),
            'taxClasses' => TaxClass::query()->orderBy('name')->get(['id', 'name']),
            'countries' => Country::query()->orderBy('name')->get(['id', 'name', 'iso2', 'emoji']),
            'states' => State::query()->with('country:id,name')->orderBy('name')->get()
                ->map(fn (State $state) => [
                    'id' => $state->id,
                    'name' => $state->name,
                    'code' => $state->code,
                    'country' => $state->country?->name,
                ]),
            'customerGroups' => CustomerGroup::query()->orderBy('name')->get(['id', 'name']),
            'urls' => [
                'update' => route('panel.settings.tax-zones.update', $taxZone),
                'destroy' => route('panel.settings.tax-zones.destroy', $taxZone),
                'index' => route('panel.settings.tax-zones.index'),
            ],
        ]);
    }

    public function update(TaxZoneRequest $request, TaxZone $taxZone, UpdatesTaxZone $updatesTaxZone): RedirectResponse
    {
        try {
            $updatesTaxZone->execute($taxZone, $request->taxZoneAttributes());
        } catch (TaxZoneActionException) {
            return back()->with('error', __('panel::tax_zones.default_unset_blocked'));
        }

        return back()->with('success', __('panel::tax_zones.flash_updated'));
    }

    public function destroy(TaxZone $taxZone, DeletesTaxZone $deletesTaxZone): RedirectResponse
    {
        try {
            $deletesTaxZone->execute($taxZone);
        } catch (TaxZoneActionException) {
            return back()->with('error', __('panel::tax_zones.delete_blocked_default'));
        }

        return redirect()->route('panel.settings.tax-zones.index')->with('success', __('panel::tax_zones.flash_deleted'));
    }
}
