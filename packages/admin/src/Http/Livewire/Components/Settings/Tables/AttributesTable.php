<?php

namespace GetCandy\Hub\Http\Livewire\Components\Settings\Tables;

use GetCandy\Facades\AttributeManifest;
use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\LivewireTables\Components\Columns\TextColumn;
use GetCandy\LivewireTables\Components\Table;
use GetCandy\Models\Attribute;
use GetCandy\Models\AttributeGroup;
use Illuminate\Support\Str;

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

            return (object) [
                'id' => Str::random(),
                'class' => class_basename($type),
                'handle' => $index,
                'group_count'     => $groups->count(),
                'attribute_count' => Attribute::whereIn(
                    'attribute_group_id',
                    $groups->pluck('id')->toArray()
                )->count(),
            ];
        });
    }
}
