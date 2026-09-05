<?php

namespace Lunar\Panel\Http\Controllers\Orders;

use Illuminate\Http\RedirectResponse;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderAddress;
use Lunar\Panel\Http\Requests\Orders\OrderAddressRequest;

/**
 * Order address corrections — customers get addresses wrong or move before
 * dispatch. Changes are recorded on the order's activity timeline with the
 * same `order-address-update` event the Filament admin writes, so both
 * surfaces share one history vocabulary.
 */
class OrderAddressController
{
    public function update(OrderAddressRequest $request, Order $order, OrderAddress $address): RedirectResponse
    {
        $previous = $address->toArray();
        $fields = array_keys($request->validated());

        $address->update($request->validated());
        $address->refresh();

        $changed = collect($previous)
            ->only($fields)
            ->filter(fn ($value, string $field) => $value != $address->getAttribute($field));

        if ($changed->isNotEmpty()) {
            activity()
                ->causedBy($request->user('staff'))
                ->performedOn($order)
                ->event('order-address-update')
                ->withProperties([
                    'fields' => $fields,
                    'type' => $address->type,
                    'new' => $address->toArray(),
                    'previous' => $previous,
                ])
                ->log('order-address-update');
        }

        return back()->with('success', __('panel::orders.flash_address_saved'));
    }
}
