<?php

namespace Lunar\Shipping\Panel\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Shipping\Contracts\Actions\ShippingExclusionLists\DeletesShippingExclusionList;
use Lunar\Shipping\Contracts\Actions\ShippingExclusionLists\UpdatesShippingExclusionList;
use Lunar\Shipping\Models\ShippingExclusionList;
use Lunar\Shipping\Panel\Http\Requests\ExclusionListRequest;
use Lunar\Shipping\Panel\Support\ExclusionRows;

class ExclusionListEditController
{
    public function __construct(protected ExclusionRows $exclusionRows) {}

    public function edit(ShippingExclusionList $shippingExclusionList): Response
    {
        return Inertia::render('shipping::settings/shipping/exclusion-lists/Edit', [
            'list' => [
                'id' => $shippingExclusionList->id,
                'name' => $shippingExclusionList->name,
                'zones' => $shippingExclusionList->shippingZones()->orderBy('name')->get()->map(fn ($zone) => [
                    'id' => $zone->id,
                    'name' => $zone->name,
                    'url' => route('panel.settings.shipping.zones.edit', $zone),
                ]),
            ],
            'products' => $this->exclusionRows->forList($shippingExclusionList),
            'urls' => [
                'update' => route('panel.settings.shipping.exclusion-lists.update', $shippingExclusionList),
                'destroy' => route('panel.settings.shipping.exclusion-lists.destroy', $shippingExclusionList),
                'index' => route('panel.settings.shipping.exclusion-lists.index'),
                'search' => route('panel.settings.shipping.exclusion-lists.products.search', $shippingExclusionList),
            ],
        ]);
    }

    public function update(ExclusionListRequest $request, ShippingExclusionList $shippingExclusionList, UpdatesShippingExclusionList $updatesShippingExclusionList): RedirectResponse
    {
        $updatesShippingExclusionList->execute($shippingExclusionList, $request->listAttributes());

        return back()->with('success', __('shipping::exclusion_lists.flash_updated'));
    }

    public function destroy(ShippingExclusionList $shippingExclusionList, DeletesShippingExclusionList $deletesShippingExclusionList): RedirectResponse
    {
        $deletesShippingExclusionList->execute($shippingExclusionList);

        return redirect()
            ->route('panel.settings.shipping.exclusion-lists.index')
            ->with('success', __('shipping::exclusion_lists.flash_deleted'));
    }
}
