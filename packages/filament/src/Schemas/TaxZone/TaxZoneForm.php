<?php

namespace Lunar\Filament\Schemas\TaxZone;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\State;
use Lunar\Core\Models\TaxZone;
use Lunar\Filament\Support\Concerns\CallsHooks;

class TaxZoneForm
{
    use CallsHooks;

    public static function configure(Schema $schema): Schema
    {
        return self::callStaticLunarHook(
            'configureForm',
            $schema->components(static::getMainComponents()),
        );
    }

    public static function getMainComponents(): array
    {
        return [
            Section::make()->schema([
                static::getNameComponent(),
                Group::make([
                    static::getActiveComponent(),
                    static::getDefaultComponent(),
                ])->columns(2),
                static::getZoneTypeComponent(),
                static::getZoneTypeCountriesComponent(),
                static::getZoneTypeCountryComponent(),
                static::getZoneTypeStatesComponent(),
                static::getZoneTypePostcodesComponent(),
            ]),
        ];
    }

    public static function getNameComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('lunar-filament::taxzone.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    public static function getZoneTypeComponent(): Component
    {
        return Select::make('zone_type')
            ->options([
                'country' => __('lunar-filament::taxzone.form.zone_type.options.country'),
                'states' => __('lunar-filament::taxzone.form.zone_type.options.states'),
                'postcodes' => __('lunar-filament::taxzone.form.zone_type.options.postcodes'),
            ])
            ->default('country')
            ->label(__('lunar-filament::taxzone.form.zone_type.label'))
            ->live()
            ->required()
            ->selectablePlaceholder(false);
    }

    public static function getZoneTypeCountriesComponent(): Component
    {
        return Select::make('zone_countries')
            ->label(__('lunar-filament::taxzone.form.zone_countries.label'))
            ->visible(fn ($get) => $get('zone_type') == 'country')
            ->dehydrated(false)
            ->options(Country::get()->pluck('name', 'iso3'))
            ->multiple()
            ->required()
            ->loadStateFromRelationshipsUsing(static function (Select $component, Model $record): void {
                $record->loadMissing('countries.country');

                $relatedModels = $record->countries;

                $component->state(
                    $relatedModels
                        ->pluck('country.iso3')
                        ->map(static fn ($key): string => strval($key))
                        ->toArray(),
                );
            })->getOptionLabelsUsing(static function (array $values): array {
                return Country::whereIn('iso3', $values)
                    ->pluck('name', 'iso3')
                    ->toArray();
            })
            ->saveRelationshipsUsing(static function (Model $record, $state) {
                $selectedCountries = Country::whereIn('iso3', $state)->get()->pluck('id');

                self::syncCountries($record, $selectedCountries);

                $record->states()->delete();
                $record->postcodes()->delete();
            });
    }

    public static function getZoneTypeCountryComponent(): Component
    {
        return Select::make('zone_country')
            ->label(__('lunar-filament::taxzone.form.zone_country.label'))
            ->visible(fn ($get) => $get('zone_type') !== 'country')
            ->dehydrated(false)
            ->required()
            ->options(Country::get()->pluck('name', 'id'))
            ->searchable()
            ->afterStateHydrated(static function (Select $component, ?Model $record): void {
                if ($record) {
                    $record->loadMissing('countries.country');

                    $relatedModels = $record->countries;

                    $component->state(
                        $relatedModels
                            ->pluck('country')
                            ->first()->id,
                    );
                }
            });
    }

    public static function getZoneTypeStatesComponent(): Component
    {
        return Select::make('zone_states')
            ->label(__('lunar-filament::taxzone.form.zone_states.label'))
            ->visible(fn ($get) => $get('zone_type') == 'states')
            ->dehydrated(false)
            ->options(fn ($get) => State::where('country_id', $get('zone_country'))->get()->pluck('name', 'code'))
            ->multiple()
            ->required()
            ->loadStateFromRelationshipsUsing(static function (Select $component, Model $record): void {
                $record->loadMissing('states.state');

                $relatedModels = $record->states;

                $component->state(
                    $relatedModels
                        ->pluck('state.code')
                        ->map(static fn ($key): string => strval($key))
                        ->toArray(),
                );
            })->getOptionLabelsUsing(static function (array $values, $get): array {
                return State::where('country_id', $get('zone_country'))
                    ->whereIn('code', $values)
                    ->pluck('name', 'code')
                    ->toArray();
            })
            ->saveRelationshipsUsing(static function (Model $record, $state, $get) {
                $selectedStates = State::where('country_id', $get('zone_country'))->whereIn('code', $state)->get()->pluck('id');

                self::syncCountries($record, [$get('zone_country')]);
                self::syncStates($record, $selectedStates);

                $record->postcodes()->delete();
            });
    }

    public static function getZoneTypePostcodesComponent(): Component
    {
        return Textarea::make('zone_postcodes')
            ->label(__('lunar-filament::taxzone.form.zone_postcodes.label'))
            ->visible(fn ($get) => $get('zone_type') == 'postcodes')
            ->dehydrated(false)
            ->rows(10)
            ->helperText(__('lunar-filament::taxzone.form.zone_postcodes.helper'))
            ->required()
            ->afterStateHydrated(static function (Textarea $component, ?Model $record): void {
                if ($record) {
                    $relatedModels = $record->postcodes;

                    $component->state(
                        $relatedModels
                            ->pluck('postcode')
                            ->join("\n"),
                    );
                }
            })
            ->saveRelationshipsUsing(static function (Model $record, $state, $get) {
                self::syncCountries($record, [$get('zone_country')]);
                self::syncPostcodes($record, $get('zone_country'), $state);

                $record->states()->delete();
            });
    }

    public static function getActiveComponent(): Component
    {
        return Toggle::make('active')
            ->label(__('lunar-filament::taxzone.form.active.label'));
    }

    public static function getDefaultComponent(): Component
    {
        return Toggle::make('default')
            ->label(__('lunar-filament::taxzone.form.default.label'));
    }

    private static function syncCountries(TaxZone $taxZone, $selectedCountries): void
    {
        $existingCountries = $taxZone->countries()->pluck('country_id');

        $countriesToAssign = collect($selectedCountries)
            ->reject(fn ($countryId) => $existingCountries->contains($countryId));

        $taxZone->countries()->createMany(
            $countriesToAssign->map(fn ($countryId) => [
                'country_id' => $countryId,
            ])
        );

        $taxZone->countries()
            ->whereNotIn('country_id', $selectedCountries)
            ->delete();
    }

    private static function syncStates(TaxZone $taxZone, $selectedStates): void
    {
        $existingStates = $taxZone->states()->pluck('state_id');

        $statesToAssign = collect($selectedStates)
            ->reject(fn ($stateId) => $existingStates->contains($stateId));

        $taxZone->states()->createMany(
            $statesToAssign->map(fn ($stateId) => [
                'state_id' => $stateId,
            ])
        );

        $taxZone->states()
            ->whereNotIn('state_id', $selectedStates)
            ->delete();
    }

    private static function syncPostcodes(TaxZone $taxZone, $countryId, $postcodes): void
    {
        $postcodes = collect(
            explode("\n", str_replace(' ', '', $postcodes))
        )->unique()->filter();

        $taxZone->postcodes()->delete();

        $taxZone->postcodes()->createMany(
            $postcodes->map(fn ($postcode) => [
                'country_id' => $countryId,
                'postcode' => $postcode,
            ])
        );
    }
}
