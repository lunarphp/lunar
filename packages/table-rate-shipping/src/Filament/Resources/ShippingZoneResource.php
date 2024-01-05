<?php

namespace Lunar\Shipping\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Shipping\Filament\Resources\ShippingZoneResource\Pages;
use Lunar\Shipping\Models\ShippingZone;

class ShippingZoneResource extends BaseResource
{
    //    protected static ?string $permission = 'settings:shipping';

    protected static ?string $model = ShippingZone::class;

    protected static ?int $navigationSort = 1;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::End;

    public static function getLabel(): string
    {
        return 'Shipping Zone';
    }

    public static function getPluralLabel(): string
    {
        return 'Shipping Zones';
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::shipping-zones');
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Shipping';
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            Pages\EditShippingZone::class,
            Pages\ManageShippingZoneRates::class,
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
        ];
    }

    public static function getNameFormComponent(): Component
    {
        return Forms\Components\TextInput::make('name')
            ->label(__('lunarpanel::taxzone.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    public static function getTypeFormComponent(): Component
    {
        return Forms\Components\Select::make('type')
            ->label('Type')
            ->required()
            ->options([
                'unrestricted' => 'Unrestricted',
                'countries' => 'Limit to Countries',
                'states' => 'Limit to States / Provinces',
                'postcodes' => 'Limit to Postcodes',
            ]);
    }

    protected static function getCountryFormComponent(): Component
    {
        return Forms\Components\Select::make('country_id')
            ->multiple()
            ->relationship(name: 'countries', titleAttribute: 'name');
    }

    protected static function getPostcodesFormComponent(): Component
    {
        return Forms\Components\Textarea::make('postcodes')
            ->rows(10)
            ->helperText('List each postcode on a new line. Supports wildcards such as NW*');
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
                ->label('Name'),
            Tables\Columns\TextColumn::make('type')
                ->label('Type'),
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
            'rates' => Pages\ManageShippingZoneRates::route('/{record}/rates'),
        ];
    }
}
