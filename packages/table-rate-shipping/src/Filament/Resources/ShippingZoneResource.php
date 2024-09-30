<?php

namespace Lunar\Shipping\Filament\Resources;

use Awcodes\Shout\Components\Shout;
use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Models\Country;
use Lunar\Models\State;
use Lunar\Shipping\Filament\Resources\ShippingZoneResource\Pages;
use Lunar\Shipping\Models\Contracts\ShippingZone;

class ShippingZoneResource extends BaseResource
{
    protected static ?string $model = ShippingZone::class;

    protected static ?int $navigationSort = 1;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::End;

    public static function getLabel(): string
    {
        return __('lunarpanel.shipping::shippingzone.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel.shipping::shippingzone.label_plural');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::shipping-zones');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel.shipping::plugin.navigation.group');
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            Pages\EditShippingZone::class,
            Pages\ManageShippingRates::class,
            Pages\ManageShippingExclusions::class,
        ]);
    }

    public static function getDefaultForm(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema(
                static::getMainFormComponents(),
            ),
        ]);
    }

    protected static function getMainFormComponents(): array
    {
        return [
            static::getNameFormComponent(),
            static::getTypeFormComponent(),
            static::getCountryFormComponent(),
            static::getPostcodesFormComponent(),
            static::getStatesFormComponent(),
            static::getCountriesFormComponent(),
            Shout::make('unrestricted')->content(
                __('lunarpanel.shipping::shippingzone.form.unrestricted.content')
            )->hidden(
                fn (Forms\Get $get) => $get('type') != 'unrestricted'
            ),
        ];
    }

    public static function getNameFormComponent(): Component
    {
        return Forms\Components\TextInput::make('name')
            ->label(__('lunarpanel.shipping::shippingzone.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    public static function getTypeFormComponent(): Component
    {
        return Forms\Components\Select::make('type')
            ->label(__('lunarpanel.shipping::shippingzone.form.type.label'))
            ->required()
            ->options([
                'unrestricted' => __('lunarpanel.shipping::shippingzone.form.type.options.unrestricted'),
                'countries' => __('lunarpanel.shipping::shippingzone.form.type.options.countries'),
                'states' => __('lunarpanel.shipping::shippingzone.form.type.options.states'),
                'postcodes' => __('lunarpanel.shipping::shippingzone.form.type.options.postcodes'),
            ])->live();
    }

    protected static function getCountryFormComponent(): Component
    {
        return Forms\Components\Select::make('country')
            ->label(__('lunarpanel.shipping::shippingzone.form.country.label'))
            ->dehydrated(false)
            ->visible(
                fn (Forms\Get $get) => ! in_array($get('type'), ['countries', 'unrestricted'])
            )
            ->options(Country::get()->pluck('name', 'id'))

            ->required()
            ->searchable()
            ->loadStateFromRelationshipsUsing(static function (Forms\Components\Select $component, Model $record): void {
                $record->loadMissing('countries');

                /** @var Collection $relatedModels */
                $country = $record->countries->first();

                $component->state(
                    $country?->id
                );
            })->getOptionLabelsUsing(static function (Model $record): array {
                $record->loadMissing('countries.country');

                return $record->countries
                    ->pluck('country.name', 'country.id')
                    ->toArray();
            })
            ->saveRelationshipsUsing(static function (Model $record, $state) {
                $selectedCountry = Country::where('id', $state)->first();

                $record->countries()->sync($selectedCountry->id);
            });
    }

    protected static function getCountriesFormComponent(): Component
    {
        return Forms\Components\Select::make('countries')
            ->label(__('lunarpanel.shipping::shippingzone.form.countries.label'))
            ->visible(fn ($get) => $get('type') == 'countries')
            ->dehydrated(false)
            ->options(Country::get()->pluck('name', 'id'))
            ->multiple()
            ->required()
            ->loadStateFromRelationshipsUsing(static function (Forms\Components\Select $component, Model $record): void {
                $record->loadMissing('countries');
                /** @var Collection $relatedModels */
                $relatedModels = $record->countries;

                $component->state(
                    $relatedModels
                        ->pluck('id')
                        ->map(static fn ($key): string => strval($key))
                        ->toArray(),
                );
            })->getOptionLabelsUsing(static function (Model $record): array {
                $record->loadMissing('countries');

                return $record->countries
                    ->pluck('name', 'id')
                    ->toArray();
            })
            ->saveRelationshipsUsing(static function (Model $record, $state) {
                $record->countries()->sync($state);
            });
    }

    protected static function getStatesFormComponent(): Component
    {
        return Forms\Components\Select::make('states')
            ->label(__('lunarpanel.shipping::shippingzone.form.states.label'))
            ->visible(fn ($get) => $get('type') == 'states')
            ->dehydrated(false)
            ->options(fn ($get) => State::where('country_id', $get('country'))->get()->pluck('name', 'id'))
            ->multiple()
            ->required()
            ->loadStateFromRelationshipsUsing(static function (Forms\Components\Select $component, Model $record): void {
                $record->loadMissing('states');

                /** @var Collection $relatedModels */
                $relatedModels = $record->states;

                $component->state(
                    $relatedModels
                        ->pluck('id')
                        ->map(static fn ($key): string => strval($key))
                        ->toArray(),
                );
            })->getOptionLabelsUsing(static function (Model $record): array {
                $record->loadMissing('states');

                return $record->states
                    ->pluck('name', 'id')
                    ->toArray();
            })
            ->saveRelationshipsUsing(static function (Model $record, $state, $get) {
                $record->states()->sync($state);
            });
    }

    protected static function getPostcodesFormComponent(): Component
    {
        return Forms\Components\Textarea::make('postcodes')
            ->label(__('lunarpanel.shipping::shippingzone.form.postcodes.label'))
            ->visible(fn ($get) => $get('type') == 'postcodes')
            ->dehydrated(false)
            ->rows(10)
            ->helperText(__('lunarpanel.shipping::shippingzone.form.postcodes.helper'))
            ->required()
            ->afterStateHydrated(static function (Forms\Components\Textarea $component, Model $record): void {
                /** @var Collection $relatedModels */
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

    private static function syncPostcodes(ShippingZone $shippingZone, $countryId, $postcodes)
    {
        $postcodes = collect(
            explode(
                "\n",
                str_replace(' ', '', $postcodes)
            )
        )->unique()->filter();

        $shippingZone->postcodes()->delete();

        $shippingZone->postcodes()->createMany(
            $postcodes->map(function ($postcode) {
                return [
                    'postcode' => $postcode,
                ];
            })
        );
    }

    public static function getDefaultTable(Table $table): Table
    {
        return $table
            ->columns(static::getTableColumns())
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')
                ->label(
                    __('lunarpanel.shipping::shippingzone.table.name.label')
                ),
            Tables\Columns\TextColumn::make('type')
                ->label(
                    __('lunarpanel.shipping::shippingzone.table.type.label')
                )
                ->formatStateUsing(
                    fn ($state) => __("lunarpanel.shipping::shippingzone.table.type.options.{$state}")
                ),
        ];
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShippingZones::route('/'),
            'edit' => Pages\EditShippingZone::route('/{record}/edit'),
            'rates' => Pages\ManageShippingRates::route('/{record}/rates'),
            'exclusions' => Pages\ManageShippingExclusions::route('/{record}/exclusions'),
        ];
    }
}
