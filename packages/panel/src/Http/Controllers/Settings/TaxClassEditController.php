<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Contracts\Actions\TaxClasses\DeletesTaxClass;
use Lunar\Core\Contracts\Actions\TaxClasses\UpdatesTaxClass;
use Lunar\Core\Exceptions\TaxClassActionException;
use Lunar\Core\Models\TaxClass;
use Lunar\Panel\Http\Requests\Settings\TaxClassRequest;

class TaxClassEditController
{
    public function edit(TaxClass $taxClass): Response
    {
        return Inertia::render('settings/tax-classes/Edit', [
            'taxClass' => [
                'id' => $taxClass->id,
                'name' => $taxClass->name,
                'default' => $taxClass->default,
            ],
            'hasVariants' => $taxClass->productVariants()->exists(),
            'urls' => [
                'update' => route('panel.settings.tax-classes.update', $taxClass),
                'destroy' => route('panel.settings.tax-classes.destroy', $taxClass),
                'index' => route('panel.settings.tax-classes.index'),
            ],
        ]);
    }

    public function update(TaxClassRequest $request, TaxClass $taxClass, UpdatesTaxClass $updatesTaxClass): RedirectResponse
    {
        try {
            $updatesTaxClass->execute($taxClass, $request->taxClassAttributes());
        } catch (TaxClassActionException) {
            return back()->with('error', __('panel::tax_classes.default_unset_blocked'));
        }

        return redirect()->route('panel.settings.tax-classes.index')->with('success', __('panel::tax_classes.flash_updated'));
    }

    public function destroy(TaxClass $taxClass, DeletesTaxClass $deletesTaxClass): RedirectResponse
    {
        try {
            $deletesTaxClass->execute($taxClass);
        } catch (TaxClassActionException) {
            return back()->with('error', $taxClass->default
                ? __('panel::tax_classes.delete_blocked_default')
                : __('panel::tax_classes.delete_blocked'));
        }

        return redirect()->route('panel.settings.tax-classes.index')->with('success', __('panel::tax_classes.flash_deleted'));
    }
}
