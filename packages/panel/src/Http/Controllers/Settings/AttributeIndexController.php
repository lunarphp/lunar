<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\AttributeGroup;
use Lunar\Panel\Http\Controllers\Concerns\ProvidesAttributeReferenceData;
use Lunar\Panel\Http\Controllers\Concerns\ResolvesTableExtensions;

class AttributeIndexController
{
    use ProvidesAttributeReferenceData;
    use ResolvesTableExtensions;

    /** @var array<int, array{key: string, label: string, width?: string, align?: string}> */
    protected array $columns = [];

    public function index(Request $request): Response
    {
        $this->columns = [
            ['key' => 'name', 'label' => __('panel::attributes_settings.column_name'), 'width' => 'minmax(0, 1.3fr)'],
            ['key' => 'handle', 'label' => __('panel::attributes_settings.column_handle'), 'width' => 'minmax(0, 1fr)'],
            ['key' => 'group', 'label' => __('panel::attributes_settings.column_group'), 'width' => 'minmax(0, 1fr)'],
            ['key' => 'type', 'label' => __('panel::attributes_settings.column_type'), 'width' => '130px'],
            ['key' => 'flags', 'label' => '', 'width' => '150px'],
        ];

        $resolver = $this->resolveTable('attributes.index');

        $attributes = Attribute::query()
            ->with('group:id,name')
            ->when($request->filled('attribute_group_id'), fn ($query) => $query->where('attribute_group_id', $request->integer('attribute_group_id')))
            ->tap(fn ($query) => $resolver->applyColumnQueries($query))
            ->tap(fn ($query) => $resolver->applyFilters($query, $request))
            ->orderBy('position')
            ->paginate(25)
            ->withQueryString()
            ->through(function (Attribute $attribute) use ($resolver): array {
                $row = [
                    'id' => $attribute->id,
                    'name' => $attribute->name,
                    'handle' => $attribute->handle,
                    'type' => $attribute->type,
                    'group' => $attribute->group?->name,
                    'position' => $attribute->position,
                    'required' => $attribute->required,
                    'searchable' => $attribute->searchable,
                    'filterable' => $attribute->filterable,
                    'system' => $attribute->system,
                    'urls' => [
                        'edit' => route('panel.settings.attributes.edit', $attribute),
                    ],
                    '_actions' => $resolver->resolveRowActionUrls($attribute),
                ];

                foreach ($resolver->getColumnKeys() as $key) {
                    $row[$key] = $attribute->getAttribute($key);
                }

                return $row;
            });

        return Inertia::render('settings/attributes/Index', [
            'attributes' => $attributes,
            ...$this->tableProps($resolver, $this->columns, $request),
            'attributeGroups' => AttributeGroup::query()->orderBy('position')->get(['id', 'name']),
            'fieldTypes' => $this->fieldTypeOptions(),
            'modelTypes' => $this->attributableModelTypes(),
            'filters' => $request->only(['attribute_group_id']),
            'urls' => [
                'index' => route('panel.settings.attributes.index'),
                'store' => route('panel.settings.attributes.store'),
            ],
        ]);
    }
}
