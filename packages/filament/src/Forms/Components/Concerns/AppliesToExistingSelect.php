<?php

namespace Lunar\Filament\Forms\Components\Concerns;

use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;
use Lunar\Filament\Forms\Components\Support\RecordSearch;

/**
 * Lets a Lunar selector inject its search/label behaviour onto an
 * existing {@see Select} instance — the shape that Filament's
 * `AttachAction::recordSelect(fn ($select) => …)` callback hands back.
 *
 * Usage:
 *
 *     AttachAction::make()->recordSelect(fn ($select) => ProductSelect::applyTo($select))
 *
 * Implementing classes must also use {@see SearchesLunarRecords} and
 * expose `lunarModel(): string` and `optionLabel(Model $record): string`.
 */
trait AppliesToExistingSelect
{
    public static function applyTo(Select $select): Select
    {
        $proxy = static::make($select->getName() ?: '__lunar_selector_proxy');

        $select->placeholder($proxy->getPlaceholder());
        $select->searchable();

        $modelClass = $proxy->lunarModel();

        $select->getSearchResultsUsing(static function (string $search) use ($proxy, $modelClass): array {
            $query = RecordSearch::for($modelClass, $search);

            return $query->take(50)->get()
                ->mapWithKeys(static fn (Model $record): array => [
                    $record->getKey() => $proxy->optionLabel($record),
                ])
                ->all();
        });

        $select->getOptionLabelUsing(static function ($value) use ($proxy, $modelClass): ?string {
            $record = $modelClass::find($value);

            return $record ? $proxy->optionLabel($record) : null;
        });

        return $select;
    }
}
