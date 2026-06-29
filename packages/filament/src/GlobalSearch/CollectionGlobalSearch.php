<?php

namespace Lunar\Filament\GlobalSearch;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\Collection;

/**
 * @extends GlobalSearchDescriptor<Collection>
 */
class CollectionGlobalSearch extends GlobalSearchDescriptor
{
    public static function getModelContract(): string
    {
        return Collection::class;
    }

    public static function getSearchableAttributes(): array
    {
        return [
            'group.name',
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'group',
        ]);
    }

    public static function getResultDetails(Model $record): array
    {
        /** @var Collection $record */
        return array_filter([
            __('lunar-filament::global-search.collections.details.group') => $record->group?->name,
            __('lunar-filament::global-search.collections.details.parent') => $record->parent?->translate('name'),
        ], fn ($value) => filled($value));
    }
}
