<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Lunar\Core\Contracts\Actions\Currencies\CreatesCurrency;
use Lunar\Panel\Http\Requests\Settings\CurrencyRequest;

class CurrencyCreateController
{
    public function store(CurrencyRequest $request, CreatesCurrency $createsCurrency): RedirectResponse
    {
        $createsCurrency->execute($request->currencyAttributes());

        return redirect()->route('panel.settings.currencies.index')->with('success', __('panel::currencies.flash_created'));
    }
}
