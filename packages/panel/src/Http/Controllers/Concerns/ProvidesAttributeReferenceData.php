<?php

namespace Lunar\Panel\Http\Controllers\Concerns;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Lunar\Core\Facades\AttributeManifest;
use Lunar\Core\Facades\FieldTypeManifest;
use Lunar\Core\Facades\ModelManifest;

/** Reference data the attribute create dialog and edit screen both need. */
trait ProvidesAttributeReferenceData
{
    /** @return array<int, array{value: string, label: string}> */
    protected function fieldTypeOptions(): array
    {
        return FieldTypeManifest::getTypes()
            ->keys()
            ->sort()
            ->map(fn (string $type) => [
                'value' => $type,
                'label' => __("panel::attributes_settings.type_{$type}"),
            ])
            ->values()
            ->all();
    }

    /**
     * The renderer-agnostic configuration field descriptors the attribute's
     * field type declares (see FieldType::getConfigurationFields()).
     *
     * @return array<int, array<string, mixed>>
     */
    protected function configurationFields(string $type): array
    {
        $class = FieldTypeManifest::getType($type);

        return $class ? (new $class)->getConfigurationFields() : [];
    }

    /**
     * Each attributable model type labelled in the staff locale. A type a store
     * registers on the AttributeManifest without a panel lang key falls back to
     * its class name as words ("GiftCard" reads "Gift Card").
     *
     * @return array<int, array{value: string, label: string}>
     */
    protected function attributableModelTypes(): array
    {
        return AttributeManifest::getTypes()
            ->map(function (string $type) {
                $key = 'panel::attributes_settings.model_type_'.Str::snake(class_basename($type));

                return [
                    'value' => ModelManifest::getMorphMapKey($type),
                    'label' => Lang::has($key) ? __($key) : Str::headline(class_basename($type)),
                ];
            })
            ->values()
            ->all();
    }
}
