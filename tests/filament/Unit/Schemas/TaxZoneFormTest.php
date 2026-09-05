<?php

use Filament\Forms\Components\Select;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Livewire\Component;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\State;
use Lunar\Filament\Schemas\TaxZone\TaxZoneForm;
use Lunar\Tests\Filament\TestCase;

uses(TestCase::class);

/**
 * Filament validates a multi-select against the labels it can resolve. Reading
 * them from the record's persisted relationship means a value the staff member
 * has just picked has no label yet, and the field is rejected as invalid.
 */
function mountedSelect(Select $component, array $state, array $siblingState = []): Select
{
    $livewire = new class extends Component implements HasSchemas
    {
        use InteractsWithSchemas;
    };

    $schema = Schema::make($livewire)
        ->statePath('data')
        ->components([$component]);

    $schema->fill([...$siblingState, $component->getName() => $state]);

    return $schema->getComponents(withHidden: true)[0];
}

it('resolves country labels from the selected values, not the saved relationship', function () {
    $selected = Country::factory()->count(2)->create();
    Country::factory()->create();

    // Nothing is attached to a tax zone — these are fresh selections.
    $component = mountedSelect(
        TaxZoneForm::getZoneTypeCountriesComponent(),
        $selected->pluck('iso3')->all(),
    );

    expect($component->getOptionLabels())
        ->toEqual($selected->pluck('name', 'iso3')->all());
});

it('resolves state labels scoped to the chosen country', function () {
    $country = Country::factory()->create();
    $other = Country::factory()->create();

    $selected = State::factory()->count(2)->create(['country_id' => $country->id]);
    $elsewhere = State::factory()->create(['country_id' => $other->id]);

    $component = mountedSelect(
        TaxZoneForm::getZoneTypeStatesComponent(),
        $selected->pluck('code')->push($elsewhere->code)->all(),
        ['zone_country' => $country->id],
    );

    // Scoped to zone_country, so a state of the same code in another country is
    // not labelled.
    expect($component->getOptionLabels())
        ->toEqual($selected->pluck('name', 'code')->all());
});
