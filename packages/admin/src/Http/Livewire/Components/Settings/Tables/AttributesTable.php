<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Tables;

use Illuminate\Support\Str;
use Lunar\Facades\AttributeManifest;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\LivewireTables\Components\Columns\TextColumn;
use Lunar\LivewireTables\Components\Table;
use Lunar\Models\Attribute;
use Lunar\Models\AttributeGroup;

class AttributesTable extends Table
{
    use Notifies;

    /**
     * {@inheritDoc}
     */
    public $hasPagination = false;

    public bool $filterable = false;

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        $this->tableBuilder->baseColumns([
            TextColumn::make('class')->heading(
                __('adminhub::tables.headings.attribute_type')
            )->url(function ($record) {
                return route('hub.attributes.show', $record->handle);
            }),
            TextColumn::make('group_count')->heading(
                __('adminhub::tables.headings.attribute_groups')
            ),
            TextColumn::make('attribute_count')->heading(
                __('adminhub::tables.headings.attributes')
            ),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        return AttributeManifest::getTypes()->map(function ($type, $index) {
            $groups = AttributeGroup::whereAttributableType($type)->get();

            $typeBaseName = class_basename($type);

            return (object) [
                'id' => Str::random(),
                'class' => __("adminhub::types.{$typeBaseName}"),
                'handle' => $index,
                'group_count' => $groups->count(),
                'attribute_count' => Attribute::whereIn(
                    'attribute_group_id',
                    $groups->pluck('id')->toArray()
                )->count(),
            ];
        });
    }
}
