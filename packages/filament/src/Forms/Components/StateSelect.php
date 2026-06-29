<?php

namespace Lunar\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\State;

/**
 * Datalist-backed state picker, dependent on a country id field. Wraps
 * Filament's `TextInput`'s datalist behaviour — the canonical pattern
 * from Lunar's address forms.
 */
class StateSelect extends TextInput
{
    protected string|Closure $countryField = 'country_id';

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('lunar-filament::forms/selectors.state.label'));
        $this->autocomplete('state');
        $this->datalist(fn (Get $get): array => $this->resolveStateOptions($get));
    }

    /**
     * Name of the country id field the datalist should follow.
     */
    public function dependsOn(string|Closure $countryField): static
    {
        $this->countryField = $countryField;

        return $this;
    }

    /**
     * @return array<int, string>
     */
    protected function resolveStateOptions(Get $get): array
    {
        $countryField = $this->evaluate($this->countryField);
        $countryId = $get($countryField);

        if (! $countryId) {
            return [];
        }

        return $this->lunarModel()::query()
            ->whereCountryId($countryId)
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }

    /**
     * @return class-string<Model>
     */
    public function lunarModel(): string
    {
        return State::class;
    }
}
