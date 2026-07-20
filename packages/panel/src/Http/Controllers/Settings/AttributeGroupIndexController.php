<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Models\AttributeGroup;
use Lunar\Panel\Http\Controllers\Concerns\ResolvesTableExtensions;

class AttributeGroupIndexController
{
    use ResolvesTableExtensions;

    /** @var array<int, array{key: string, label: string, width?: string, align?: string}> */
    protected array $columns = [];

    public function index(Request $request): Response
    {
        $this->columns = [
            ['key' => 'name', 'label' => __('panel::attribute_groups.column_name'), 'width' => 'minmax(0, 1.4fr)'],
            ['key' => 'handle', 'label' => __('panel::attribute_groups.column_handle'), 'width' => 'minmax(0, 1fr)'],
            ['key' => 'attributes_count', 'label' => __('panel::attribute_groups.column_attributes'), 'width' => '110px', 'align' => 'right'],
        ];

        $resolver = $this->resolveTable('attribute-groups.index');

        $attributeGroups = AttributeGroup::query()
            ->withCount('attributes')
            ->tap(fn ($query) => $resolver->applyColumnQueries($query))
            ->tap(fn ($query) => $resolver->applyFilters($query, $request))
            ->orderBy('position')
            ->paginate(25)
            ->withQueryString()
            ->through(function (AttributeGroup $attributeGroup) use ($resolver): array {
                $row = [
                    'id' => $attributeGroup->id,
                    'name' => $attributeGroup->name,
                    'handle' => $attributeGroup->handle,
                    'position' => $attributeGroup->position,
                    'system' => $attributeGroup->system,
                    'attributes_count' => (int) $attributeGroup->getAttribute('attributes_count'),
                    'urls' => [
                        'edit' => route('panel.settings.attribute-groups.edit', $attributeGroup),
                    ],
                    '_actions' => $resolver->resolveRowActionUrls($attributeGroup),
                ];

                foreach ($resolver->getColumnKeys() as $key) {
                    $row[$key] = $attributeGroup->getAttribute($key);
                }

                return $row;
            });

        return Inertia::render('settings/attribute-groups/Index', [
            'attributeGroups' => $attributeGroups,
            ...$this->tableProps($resolver, $this->columns, $request),
            'urls' => [
                'index' => route('panel.settings.attribute-groups.index'),
                'store' => route('panel.settings.attribute-groups.store'),
            ],
        ]);
    }
}
