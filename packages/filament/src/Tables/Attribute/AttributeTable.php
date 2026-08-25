<?php

namespace Lunar\Filament\Tables\Attribute;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Lunar\Core\Models\AttributeGroup;
use Lunar\Filament\Actions\Attributes\DeleteAttributeAction;
use Lunar\Filament\Actions\Attributes\DeleteAttributesBulkAction;
use Lunar\Filament\Support\Concerns\CallsHooks;

/**
 * Table for the standalone attributes surface (spec 0063). Lists every
 * attribute — grouped or not — with a group filter that can isolate
 * ungrouped attributes. The attribute group relation manager reuses the
 * column helpers, dropping the group column and filter.
 */
class AttributeTable
{
    use CallsHooks;

    /**
     * Group filter option that matches attributes with no group.
     */
    public const UNGROUPED_FILTER_VALUE = 'ungrouped';

    public static function configure(Table $table): Table
    {
        return self::callStaticLunarHook(
            'configureTable',
            $table
                ->columns(static::getColumns())
                ->filters([
                    static::getGroupFilter(),
                ])
                ->recordActions([
                    EditAction::make(),
                    DeleteAttributeAction::make(),
                ])
                ->toolbarActions([
                    BulkActionGroup::make([
                        DeleteAttributesBulkAction::make(),
                    ]),
                ])
                ->defaultSort('position', 'asc'),
        );
    }

    public static function getColumns(): array
    {
        return [
            static::getNameColumn(),
            static::getHandleColumn(),
            static::getTypeColumn(),
            static::getGroupColumn(),
        ];
    }

    public static function getNameColumn(): TextColumn
    {
        return TextColumn::make('name')
            ->label(__('lunar-filament::attribute.table.name.label'));
    }

    public static function getHandleColumn(): TextColumn
    {
        return TextColumn::make('handle')
            ->label(__('lunar-filament::attribute.table.handle.label'));
    }

    public static function getTypeColumn(): TextColumn
    {
        return TextColumn::make('type')
            ->label(__('lunar-filament::attribute.table.type.label'));
    }

    public static function getGroupColumn(): TextColumn
    {
        return TextColumn::make('group.name')
            ->label(__('lunar-filament::attribute.table.group.label'))
            ->badge()
            ->placeholder(__('lunar-filament::attribute.table.group.ungrouped'));
    }

    public static function getGroupFilter(): SelectFilter
    {
        return SelectFilter::make('attribute_group_id')
            ->label(__('lunar-filament::attribute.table.group.label'))
            ->options(
                fn () => AttributeGroup::query()
                    ->orderBy('position')
                    ->pluck('name', 'id')
                    ->prepend(__('lunar-filament::attribute.table.group.ungrouped'), self::UNGROUPED_FILTER_VALUE)
                    ->all()
            )
            ->query(function (Builder $query, array $data) {
                $value = $data['value'] ?? null;

                if (blank($value)) {
                    return;
                }

                $value === self::UNGROUPED_FILTER_VALUE
                    ? $query->whereNull('attribute_group_id')
                    : $query->where('attribute_group_id', $value);
            });
    }
}
