<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Contracts\Actions\Currencies\DeletesCurrency;
use Lunar\Core\Contracts\Actions\Currencies\UpdatesCurrency;
use Lunar\Core\Exceptions\CurrencyActionException;
use Lunar\Core\Models\Currency;
use Lunar\Panel\Http\Requests\Settings\CurrencyRequest;

class CurrencyEditController
{
    public function edit(Currency $currency): Response
    {
        return Inertia::render('settings/currencies/Edit', [
            'currency' => [
                'id' => $currency->id,
                'code' => $currency->code,
                'name' => $currency->name,
                'exchange_rate' => (float) $currency->exchange_rate,
                'decimal_places' => $currency->decimal_places,
                'enabled' => $currency->enabled,
                'default' => $currency->default,
                'sync_prices' => $currency->sync_prices,
            ],
            'hasPrices' => $currency->prices()->exists(),
            'urls' => [
                'update' => route('panel.settings.currencies.update', $currency),
                'destroy' => route('panel.settings.currencies.destroy', $currency),
                'index' => route('panel.settings.currencies.index'),
            ],
        ]);
    }

    public function update(CurrencyRequest $request, Currency $currency, UpdatesCurrency $updatesCurrency): RedirectResponse
    {
        try {
            $updatesCurrency->execute($currency, $attributes = $request->currencyAttributes());
        } catch (CurrencyActionException) {
            // Mirrors UpdateCurrency's guards: an explicit default=false on the
            // default currency is the unset case; every other throw is the
            // disable-default guard.
            $attemptedUnset = $currency->default
                && array_key_exists('default', $attributes)
                && ! $attributes['default'];

            return back()->with('error', $attemptedUnset
                ? __('panel::currencies.default_unset_blocked')
                : __('panel::currencies.default_disable_blocked'));
        }

        return redirect()->route('panel.settings.currencies.index')->with('success', __('panel::currencies.flash_updated'));
    }

    public function destroy(Currency $currency, DeletesCurrency $deletesCurrency): RedirectResponse
    {
        try {
            $deletesCurrency->execute($currency);
        } catch (CurrencyActionException) {
            return back()->with('error', $currency->default
                ? __('panel::currencies.delete_blocked_default')
                : __('panel::currencies.delete_blocked'));
        }

        return redirect()->route('panel.settings.currencies.index')->with('success', __('panel::currencies.flash_deleted'));
    }
}
