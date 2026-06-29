<?php

namespace Lunar\Filament\Forms\Components;

use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\Tag;
use Lunar\Filament\Forms\Components\Concerns\ExcludesAttachedRecords;

class TagSelect extends Select
{
    use ExcludesAttachedRecords;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('lunar-filament::forms/selectors.tag.label'));
        $this->placeholder(__('lunar-filament::forms/selectors.tag.placeholder'));
        $this->multiple();
        $this->searchable();
        $this->getSearchResultsUsing(function (string $search): array {
            $model = $this->lunarModel();

            return $model::query()
                ->where('value', 'like', "%{$search}%")
                ->take(50)
                ->get()
                ->mapWithKeys(fn (Model $record): array => [
                    $record->getKey() => $record->value,
                ])
                ->all();
        });
        $this->getOptionLabelUsing(fn ($value): ?string => $this->lunarModel()::find($value)?->value);
    }

    /**
     * @return class-string<Model>
     */
    public function lunarModel(): string
    {
        return Tag::class;
    }
}
