<?php

namespace Lunar\Shipping\Filament\Resources\ShippingZoneResource\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\State;
use Lunar\Filament\Support\Concerns\CallsHooks;
use Lunar\Shipping\Models\ShippingZone;

class ShippingZoneForm
{
    use CallsHooks;

    public static function configure(Schema $schema): Schema
    {
        return self::callStaticLunarHook(
            'configureForm',
            $schema->components([
                Section::make()->schema(static::getMainComponents()),
            ]),
        );
    }

    public static function getMainComponents(): array
    {
        return [
            static::getNameComponent(),
            static::getTypeComponent(),
            static::getCountryComponent(),
            static::getPostcodesComponent(),
            static::getStatesComponent(),
            static::getCountriesComponent(),
            Callout::make()
                ->status('info')
                ->heading(__('lunarpanel.shipping::shippingzone.form.unrestricted.content'))
                ->hidden(fn (Get $get) => $get('type') != 'unrestricted'),
        ];
    }

    public static function getNameComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('lunarpanel.shipping::shippingzone.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    public static function getTypeComponent(): Component
    {
        return Select::make('type')
            ->label(__('lunarpanel.shipping::shippingzone.form.type.label'))
            ->required()
            ->options([
                'unrestricted' => __('lunarpanel.shipping::shippingzone.form.type.options.unrestricted'),
                'countries' => __('lunarpanel.shipping::shippingzone.form.type.options.countries'),
                'states' => __('lunarpanel.shipping::shippingzone.form.type.options.states'),
                'postcodes' => __('lunarpanel.shipping::shippingzone.form.type.options.postcodes'),
            ])->live();
    }

    public static function getCountryComponent(): Component
    {
        return Select::make('country')
            ->label(__('lunarpanel.shipping::shippingzone.form.country.label'))
            ->dehydrated(false)
            ->visible(fn (Get $get) => ! in_array($get('type'), ['countries', 'unrestricted']))
            ->options(Country::get()->pluck('name', 'id'))
            ->required()
            ->searchable()
            ->loadStateFromRelationshipsUsing(static function (Select $component, Model $record): void {
                $record->loadMissing('countries');

                $country = $record->countries->first();

                $component->state($country?->id);
            })->getOptionLabelsUsing(static function (array $values): array {
                return Country::whereIn('id', $values)
                    ->pluck('name', 'id')
                    ->toArray();
            })
            ->saveRelationshipsUsing(static function (Model $record, $state) {
                $selectedCountry = Country::where('id', $state)->first();

                $record->countries()->sync($selectedCountry->id);
            });
    }

    public static function getCountriesComponent(): Component
    {
        return Select::make('countries')
            ->label(__('lunarpanel.shipping::shippingzone.form.countries.label'))
            ->visible(fn ($get) => $get('type') == 'countries')
            ->dehydrated(false)
            ->options(Country::get()->pluck('name', 'id'))
            ->multiple()
            ->required()
            ->loadStateFromRelationshipsUsing(static function (Select $component, Model $record): void {
                $record->loadMissing('countries');
                $relatedModels = $record->countries;

                $component->state(
                    $relatedModels
                        ->pluck('id')
                        ->map(static fn ($key): string => strval($key))
                        ->toArray(),
                );
            })->getOptionLabelsUsing(static function (array $values): array {
                return Country::whereIn('id', $values)
                    ->pluck('name', 'id')
                    ->toArray();
            })
            ->saveRelationshipsUsing(static function (Model $record, $state) {
                $record->countries()->sync($state);
            });
    }

    public static function getStatesComponent(): Component
    {
        return Select::make('states')
            ->label(__('lunarpanel.shipping::shippingzone.form.states.label'))
            ->visible(fn ($get) => $get('type') == 'states')
            ->dehydrated(false)
            ->options(fn ($get) => State::where('country_id', $get('country'))->get()->pluck('name', 'id'))
            ->multiple()
            ->required()
            ->loadStateFromRelationshipsUsing(static function (Select $component, Model $record): void {
                $record->loadMissing('states');

                $relatedModels = $record->states;

                $component->state(
                    $relatedModels
                        ->pluck('id')
                        ->map(static fn ($key): string => strval($key))
                        ->toArray(),
                );
            })->getOptionLabelsUsing(static function (array $values): array {
                return State::whereIn('id', $values)
                    ->pluck('name', 'id')
                    ->toArray();
            })
            ->saveRelationshipsUsing(static function (Model $record, $state, $get) {
                $record->states()->sync($state);
            });
    }

    public static function getPostcodesComponent(): Component
    {
        return Textarea::make('postcodes')
            ->label(__('lunarpanel.shipping::shippingzone.form.postcodes.label'))
            ->visible(fn ($get) => $get('type') == 'postcodes')
            ->dehydrated(false)
            ->rows(10)
            ->helperText(__('lunarpanel.shipping::shippingzone.form.postcodes.helper'))
            ->required()
            ->afterStateHydrated(static function (Textarea $component, Model $record): void {
                $relatedModels = $record->postcodes;

                $component->state(
                    $relatedModels
                        ->pluck('postcode')
                        ->join("\n"),
                );
            })
            ->saveRelationshipsUsing(static function (Model $record, $state, $get) {
                static::syncPostcodes($record, $get('zone_country'), $state);

                $record->states()->detach();
            });
    }

    private static function syncPostcodes(ShippingZone $shippingZone, $countryId, $postcodes): void
    {
        $postcodes = collect(
            explode("\n", str_replace(' ', '', $postcodes))
        )->unique()->filter();

        $shippingZone->postcodes()->delete();

        $shippingZone->postcodes()->createMany(
            $postcodes->map(fn ($postcode) => ['postcode' => $postcode])
        );
    }
}
