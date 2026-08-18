<?php

namespace Lunar\Panel\Http\Controllers\Concerns;

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

    /** @return array<int, array{value: string, label: string}> */
    protected function attributableModelTypes(): array
    {
        return AttributeManifest::getTypes()
            ->map(fn (string $type) => [
                'value' => ModelManifest::getMorphMapKey($type),
                'label' => class_basename($type),
            ])
            ->values()
            ->all();
    }
}
