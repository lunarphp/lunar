<?php

namespace Lunar\Shipping\Panel\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Lunar\Shipping\Contracts\Actions\ShippingExclusionLists\CreatesShippingExclusionList;
use Lunar\Shipping\Panel\Http\Requests\ExclusionListRequest;

class ExclusionListCreateController
{
    public function store(ExclusionListRequest $request, CreatesShippingExclusionList $createsShippingExclusionList): RedirectResponse
    {
        $attributes = $request->listAttributes();

        unset($attributes['products']);

        $list = $createsShippingExclusionList->execute($attributes);

        return redirect()
            ->route('panel.settings.shipping.exclusion-lists.edit', $list)
            ->with('success', __('shipping::exclusion_lists.flash_created'));
    }
}
