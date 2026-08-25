<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Contracts\Actions\Attributes\DeletesAttribute;
use Lunar\Core\Contracts\Actions\Attributes\UpdatesAttribute;
use Lunar\Core\Exceptions\AttributeActionException;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\AttributeGroup;
use Lunar\Panel\Http\Controllers\Concerns\ProvidesAttributeReferenceData;
use Lunar\Panel\Http\Requests\Settings\AttributeRequest;

class AttributeEditController
{
    use ProvidesAttributeReferenceData;

    public function edit(Attribute $attribute): Response
    {
        return Inertia::render('settings/attributes/Edit', [
            'attribute' => [
                'id' => $attribute->id,
                'name' => $attribute->name,
                'handle' => $attribute->handle,
                'type' => $attribute->type,
                'attribute_group_id' => $attribute->attribute_group_id,
                'position' => $attribute->position,
                'required' => $attribute->required,
                'validation_rules' => $attribute->validation_rules ?? [],
                'searchable' => $attribute->searchable,
                'filterable' => $attribute->filterable,
                'system' => $attribute->system,
                'configuration' => $attribute->configuration ?? collect(),
                'model_types' => $attribute->models()->pluck('model_type'),
            ],
            'attributeGroups' => AttributeGroup::query()->orderBy('position')->get(['id', 'name']),
            'configFields' => $this->configurationFields($attribute->type),
            'fieldTypes' => $this->fieldTypeOptions(),
            'modelTypes' => $this->attributableModelTypes(),
            'urls' => [
                'update' => route('panel.settings.attributes.update', $attribute),
                'destroy' => route('panel.settings.attributes.destroy', $attribute),
                'index' => route('panel.settings.attributes.index'),
            ],
        ]);
    }

    public function update(AttributeRequest $request, Attribute $attribute, UpdatesAttribute $updatesAttribute): RedirectResponse
    {
        $updatesAttribute->execute($attribute, $request->attributeAttributes());

        return redirect()->route('panel.settings.attributes.index')->with('success', __('panel::attributes_settings.flash_updated'));
    }

    public function destroy(Attribute $attribute, DeletesAttribute $deletesAttribute): RedirectResponse
    {
        try {
            $deletesAttribute->execute($attribute);
        } catch (AttributeActionException) {
            return back()->with('error', __('panel::attributes_settings.delete_blocked_system'));
        }

        return redirect()->route('panel.settings.attributes.index')->with('success', __('panel::attributes_settings.flash_deleted'));
    }
}
