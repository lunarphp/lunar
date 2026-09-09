<?php

namespace Lunar\Shipping\Panel\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Facades\Converter;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Shipping\Contracts\Actions\ShippingMethods\DeletesShippingMethod;
use Lunar\Shipping\Contracts\Actions\ShippingMethods\UpdatesShippingMethod;
use Lunar\Shipping\Models\ShippingMethod;
use Lunar\Shipping\Panel\Http\Requests\MethodRequest;
use Lunar\Shipping\Panel\Support\CurrencyOptions;
use Lunar\Shipping\Panel\Support\DriverOptions;
use Lunar\Shipping\Panel\Support\MethodData;

class MethodEditController
{
    public function __construct(
        protected DriverOptions $driverOptions,
        protected CurrencyOptions $currencyOptions,
        protected MethodData $methodData,
    ) {}

    public function edit(ShippingMethod $shippingMethod): Response
    {
        $groups = $shippingMethod->customerGroups()->get()->keyBy('id');

        return Inertia::render('shipping::settings/shipping/methods/Edit', [
            'method' => [
                'id' => $shippingMethod->id,
                'name' => $shippingMethod->name,
                'code' => $shippingMethod->code,
                'driver' => $shippingMethod->driver,
                'description' => $shippingMethod->description,
                'stock_available' => (bool) $shippingMethod->stock_available,
                'weight_unit' => $shippingMethod->weight_unit,
                'min_weight' => $shippingMethod->min_weight,
                'max_weight' => $shippingMethod->max_weight,
                'data' => $this->methodData->toForm((array) $shippingMethod->data),
            ],
            'drivers' => $this->driverOptions->all(),
            'weightUnits' => array_keys(Converter::getMeasurements()['weight'] ?? []),
            'currencies' => $this->currencyOptions->all(),
            'customerGroups' => CustomerGroup::query()->orderBy('name')->get()->map(function (CustomerGroup $group) use ($groups) {
                $pivot = $groups->get($group->id)?->pivot;

                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'enabled' => (bool) ($pivot?->enabled ?? false),
                    'visible' => (bool) ($pivot?->visible ?? false),
                    'starts_at' => $pivot?->starts_at ? substr((string) $pivot->starts_at, 0, 10) : null,
                    'ends_at' => $pivot?->ends_at ? substr((string) $pivot->ends_at, 0, 10) : null,
                ];
            }),
            'urls' => [
                'update' => route('panel.settings.shipping.methods.update', $shippingMethod),
                'destroy' => route('panel.settings.shipping.methods.destroy', $shippingMethod),
                'index' => route('panel.settings.shipping.methods.index'),
            ],
        ]);
    }

    public function update(MethodRequest $request, ShippingMethod $shippingMethod, UpdatesShippingMethod $updatesShippingMethod): RedirectResponse
    {
        $attributes = $request->methodAttributes();

        if (($data = $request->driverData()) !== null) {
            $attributes['data'] = $this->methodData->toStorage($data);
        }

        if (($rows = $request->customerGroupRows()) !== null) {
            $attributes['customer_groups'] = $rows;
        }

        $updatesShippingMethod->execute($shippingMethod, $attributes);

        return back()->with('success', __('shipping::methods.flash_updated'));
    }

    public function destroy(ShippingMethod $shippingMethod, DeletesShippingMethod $deletesShippingMethod): RedirectResponse
    {
        $deletesShippingMethod->execute($shippingMethod);

        return redirect()
            ->route('panel.settings.shipping.methods.index')
            ->with('success', __('shipping::methods.flash_deleted'));
    }
}
