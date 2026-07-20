<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Lunar\Core\Contracts\Actions\TaxClasses\CreatesTaxClass;
use Lunar\Panel\Http\Requests\Settings\TaxClassRequest;

class TaxClassCreateController
{
    public function store(TaxClassRequest $request, CreatesTaxClass $createsTaxClass): RedirectResponse
    {
        $createsTaxClass->execute($request->taxClassAttributes());

        return redirect()->route('panel.settings.tax-classes.index')->with('success', __('panel::tax_classes.flash_created'));
    }
}
