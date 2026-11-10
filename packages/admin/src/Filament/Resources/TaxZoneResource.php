<?php

namespace Lunar\Admin\Filament\Resources;

use Awcodes\BadgeableColumn\Components\Badge;
use Awcodes\BadgeableColumn\Components\BadgeableColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Clusters\Taxes;
use Lunar\Admin\Filament\Resources\TaxZoneResource\Pages\CreateTaxZone;
use Lunar\Admin\Filament\Resources\TaxZoneResource\Pages\EditTaxZone;
use Lunar\Admin\Filament\Resources\TaxZoneResource\Pages\ListTaxZones;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Models\Contracts\TaxZone as TaxZoneContract;
use Lunar\Models\Country;
use Lunar\Models\State;

class TaxZoneResource extends BaseResource
{
    protected static ?string $cluster = Taxes::class;

    protected static ?string $permission = 'settings:core';

    protected static ?string $model = TaxZoneContract::class;

    protected static ?int $navigationSort = 1;

    public static function getLabel(): string
    {
        return __('lunarpanel::taxzone.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::taxzone.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::tax');
    }

    protected static function getMainFormComponents(): array
    {
        return [
            Section::make()->schema([
                static::getNameFormComponent(),
                static::getPriceDisplayFormComponent(),
                Group::make([
                    static::getActiveFormComponent(),
                    static::getDefaultFormComponent(),
                ])->columns(2),
                static::getZoneTypeFormComponent(),
                static::getZoneTypeCountriesFormComponent(),
                static::getZoneTypeCountryFormComponent(),
                static::getZoneTypeStatesFormComponent(),
                static::getZoneTypePostcodesFormComponent(),
            ]),
        ];
    }

    public static function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('lunarpanel::taxzone.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    public static function getZoneTypeFormComponent(): Component
    {
        return Select::make('zone_type')
            ->options([
                'country' => __('lunarpanel::taxzone.form.zone_type.options.country'),
                'states' => __('lunarpanel::taxzone.form.zone_type.options.states'),
                'postcodes' => __('lunarpanel::taxzone.form.zone_type.options.postcodes'),
            ])
            ->default('country')
            ->label(__('lunarpanel::taxzone.form.zone_type.label'))
            ->live()
            ->required()
            ->selectablePlaceholder(false);
    }

    protected static function getZoneTypeCountriesFormComponent(): Component
    {
        return Select::make('zone_countries')
            ->label(__('lunarpanel::taxzone.form.zone_countries.label'))
            ->visible(fn ($get) => $get('zone_type') == 'country')
            ->dehydrated(false)
            ->options(Country::get()->pluck('name', 'iso3'))
            ->multiple()
            ->required()
            ->loadStateFromRelationshipsUsing(static function (Select $component, Model $record): void {
                $record->loadMissing('countries.country');

                /** @var Collection $relatedModels */
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

    protected static function getZoneTypeCountryFormComponent(): Component
    {
        return Select::make('zone_country')
            ->label(__('lunarpanel::taxzone.form.zone_country.label'))
            ->visible(fn ($get) => $get('zone_type') !== 'country')
            ->dehydrated(false)
            ->required()
            ->options(Country::get()->pluck('name', 'id'))
            ->searchable()
            ->afterStateHydrated(static function (Select $component, ?Model $record): void {
                if ($record) {
                    $record->loadMissing('countries.country');

                    /** @var Collection $relatedModels */
                    $relatedModels = $record->countries;

                    $component->state(
                        $relatedModels
                            ->pluck('country')
                            ->first()->id,
                    );
                }
            });
    }

    protected static function getZoneTypeStatesFormComponent(): Component
    {
        return Select::make('zone_states')
            ->label(__('lunarpanel::taxzone.form.zone_states.label'))
            ->visible(fn ($get) => $get('zone_type') == 'states')
            ->dehydrated(false)
            ->options(fn ($get) => State::where('country_id', $get('zone_country'))->get()->pluck('name', 'code'))
            ->multiple()
            ->required()
            ->loadStateFromRelationshipsUsing(static function (Select $component, Model $record): void {
                $record->loadMissing('states.state');

                /** @var Collection $relatedModels */
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

    protected static function getZoneTypePostcodesFormComponent(): Component
    {
        return Textarea::make('zone_postcodes')
            ->label(__('lunarpanel::taxzone.form.zone_postcodes.label'))
            ->visible(fn ($get) => $get('zone_type') == 'postcodes')
            ->dehydrated(false)
            ->rows(10)
            ->helperText(__('lunarpanel::taxzone.form.zone_postcodes.helper'))
            ->required()
            ->afterStateHydrated(static function (Textarea $component, ?Model $record): void {
                if ($record) {
                    /** @var Collection $relatedModels */
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

    private static function syncCountries(TaxZoneContract $taxZone, $selectedCountries)
    {
        $existingCountries = $taxZone->countries()->pluck('country_id');

        $countriesToAssign = collect($selectedCountries)
            ->reject(function ($countryId) use ($existingCountries) {
                return $existingCountries->contains($countryId);
            });

        $taxZone->countries()->createMany(
            $countriesToAssign->map(fn ($countryId) => [
                'country_id' => $countryId,
            ])
        );

        $taxZone->countries()
            ->whereNotIn('country_id', $selectedCountries)
            ->delete();
    }

    private static function syncStates(TaxZoneContract $taxZone, $selectedStates)
    {
        $existingStates = $taxZone->states()->pluck('state_id');

        $statesToAssign = collect($selectedStates)
            ->reject(function ($stateId) use ($existingStates) {
                return $existingStates->contains($stateId);
            });

        $taxZone->states()->createMany(
            $statesToAssign->map(fn ($stateId) => [
                'state_id' => $stateId,
            ])
        );

        $taxZone->states()
            ->whereNotIn('state_id', $selectedStates)
            ->delete();
    }

    private static function syncPostcodes(TaxZoneContract $taxZone, $countryId, $postcodes)
    {
        $postcodes = collect(
            explode(
                "\n",
                str_replace(' ', '', $postcodes)
            )
        )->unique()->filter();

        $taxZone->postcodes()->delete();

        $taxZone->postcodes()->createMany(
            $postcodes->map(function ($postcode) use ($countryId) {
                return [
                    'country_id' => $countryId,
                    'postcode' => $postcode,
                ];
            })
        );
    }

    public static function getPriceDisplayFormComponent(): Component
    {
        return Select::make('price_display')
            ->options([
                'tax_inclusive' => __('lunarpanel::taxzone.form.price_display.options.include_tax'),
                'tax_exclusive' => __('lunarpanel::taxzone.form.price_display.options.exclude_tax'),
            ])
            ->label(__('lunarpanel::taxzone.form.price_display.label'))
            ->required();
    }

    protected static function getActiveFormComponent(): Component
    {
        return Toggle::make('active')
            ->label(__('lunarpanel::taxzone.form.active.label'));
    }

    protected static function getDefaultFormComponent(): Component
    {
        return Toggle::make('default')
            ->label(__('lunarpanel::taxzone.form.default.label'));
    }

    public static function getDefaultTable(Table $table): Table
    {
        return $table
            ->columns(static::getTableColumns())
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function getTableColumns(): array
    {
        return [
            BadgeableColumn::make('name')
                ->separator('')
                ->suffixBadges([
                    Badge::make('default')
                        ->label(__('lunarpanel::taxzone.table.default.label'))
                        ->color('gray')
                        ->visible(fn (Model $record) => $record->default),
                ])
                ->label(__('lunarpanel::taxzone.table.name.label')),
            TextColumn::make('zone_type')
                ->label(__('lunarpanel::taxzone.table.zone_type.label'))
                ->formatStateUsing(
                    fn ($state) => __("lunarpanel::taxzone.form.zone_type.options.{$state}")
                ),
            IconColumn::make('active')
                ->boolean()
                ->label(__('lunarpanel::taxzone.table.active.label')),
        ];
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getDefaultPages(): array
    {
        return [
            'index' => ListTaxZones::route('/'),
            'edit' => EditTaxZone::route('/{record}/edit'),
            'create' => CreateTaxZone::route('/create'),
        ];
    }
}
