<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Lunar\Core\Contracts\Actions\Attributes\CreatesAttribute;
use Lunar\Panel\Http\Requests\Settings\AttributeRequest;

class AttributeCreateController
{
    public function store(AttributeRequest $request, CreatesAttribute $createsAttribute): RedirectResponse
    {
        $attribute = $createsAttribute->execute($request->attributeAttributes());

        return redirect()
            ->route('panel.settings.attributes.edit', $attribute)
            ->with('success', __('panel::attributes_settings.flash_created'));
    }
}
