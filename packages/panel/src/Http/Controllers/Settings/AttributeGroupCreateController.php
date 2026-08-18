<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Lunar\Core\Contracts\Actions\Attributes\CreatesAttributeGroup;
use Lunar\Panel\Http\Requests\Settings\AttributeGroupRequest;

class AttributeGroupCreateController
{
    public function store(AttributeGroupRequest $request, CreatesAttributeGroup $createsAttributeGroup): RedirectResponse
    {
        $createsAttributeGroup->execute($request->attributeGroupAttributes());

        return redirect()->route('panel.settings.attribute-groups.index')->with('success', __('panel::attribute_groups.flash_created'));
    }
}
