<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Contracts\Actions\Attributes\DeletesAttributeGroup;
use Lunar\Core\Contracts\Actions\Attributes\UpdatesAttributeGroup;
use Lunar\Core\Exceptions\AttributeActionException;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\AttributeGroup;
use Lunar\Panel\Http\Requests\Settings\AttributeGroupRequest;

class AttributeGroupEditController
{
    public function edit(AttributeGroup $attributeGroup): Response
    {
        return Inertia::render('settings/attribute-groups/Edit', [
            'attributeGroup' => [
                'id' => $attributeGroup->id,
                'name' => $attributeGroup->name,
                'handle' => $attributeGroup->handle,
                'position' => $attributeGroup->position,
                'system' => $attributeGroup->system,
            ],
            'attributes' => $attributeGroup->attributes()
                ->orderBy('position')
                ->get()
                ->map(fn (Attribute $attribute) => [
                    'id' => $attribute->id,
                    'name' => $attribute->name,
                    'handle' => $attribute->handle,
                    'type' => $attribute->type,
                    'system' => $attribute->system,
                    'urls' => [
                        'edit' => route('panel.settings.attributes.edit', $attribute),
                    ],
                ]),
            'urls' => [
                'update' => route('panel.settings.attribute-groups.update', $attributeGroup),
                'destroy' => route('panel.settings.attribute-groups.destroy', $attributeGroup),
                'index' => route('panel.settings.attribute-groups.index'),
            ],
        ]);
    }

    public function update(AttributeGroupRequest $request, AttributeGroup $attributeGroup, UpdatesAttributeGroup $updatesAttributeGroup): RedirectResponse
    {
        $updatesAttributeGroup->execute($attributeGroup, $request->attributeGroupAttributes());

        return redirect()->route('panel.settings.attribute-groups.index')->with('success', __('panel::attribute_groups.flash_updated'));
    }

    public function destroy(AttributeGroup $attributeGroup, DeletesAttributeGroup $deletesAttributeGroup): RedirectResponse
    {
        try {
            $deletesAttributeGroup->execute($attributeGroup);
        } catch (AttributeActionException) {
            return back()->with('error', $attributeGroup->system
                ? __('panel::attribute_groups.delete_blocked_system')
                : __('panel::attribute_groups.delete_blocked'));
        }

        return redirect()->route('panel.settings.attribute-groups.index')->with('success', __('panel::attribute_groups.flash_deleted'));
    }
}
