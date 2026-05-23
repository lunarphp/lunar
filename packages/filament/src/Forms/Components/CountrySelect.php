<?php

namespace Lunar\Filament\Forms\Components;

use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\Country;

class CountrySelect extends Select
{
    protected bool $useIso3 = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('lunar-filament::forms/selectors.country.label'));
        $this->placeholder(__('lunar-filament::forms/selectors.country.placeholder'));
        $this->relationship('country', 'name');
        $this->getOptionLabelFromRecordUsing(fn (Model $record): string => static::formatCountryLabel($record));
        $this->searchable();
        $this->preload();
    }

    /**
     * Switch to ISO3 mode — values stored as `iso3` codes rather than
     * primary keys. Use when the field needs to round-trip an ISO3
     * column (e.g. TaxZone's country join).
     */
    public function iso3(bool $condition = true): static
    {
        $this->useIso3 = $condition;

        if ($condition) {
            $modelClass = $this->lunarModel();

            $this->relationship(null);
            $this->options(fn (): array => $modelClass::query()
                ->orderBy('name')
                ->get()
                ->mapWithKeys(fn (Model $record): array => [
                    $record->iso3 => static::formatCountryLabel($record),
                ])
                ->all());
        }

        return $this;
    }

    public static function formatCountryLabel(Model $record): string
    {
        $name = $record->native ?: $record->name;

        return trim(($record->emoji ?? '').' '.$name);
    }

    /**
     * @return class-string<Model>
     */
    public function lunarModel(): string
    {
        return Country::modelClass();
    }
}
