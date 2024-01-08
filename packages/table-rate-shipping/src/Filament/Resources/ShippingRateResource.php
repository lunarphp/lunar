<?php

namespace Lunar\Shipping\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Shipping\Filament\Resources\ShippingMethodResource\Pages;
use Lunar\Shipping\Models\ShippingRate;

class ShippingRateResource extends BaseResource
{
    //    protected static ?string $permission = 'settings:shipping';

    protected static ?string $model = ShippingRate::class;

    protected static ?int $navigationSort = 1;

    public static function getLabel(): string
    {
        return 'Shipping Rate';
    }

    public static function getPluralLabel(): string
    {
        return 'Shipping Rates';
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
            Forms\Components\Group::make([
                static::getCodeFormComponent(),
                static::getDriverFormComponent(),
            ])->columns(2),
            Forms\Components\Group::make([
                static::getCutoffFormComponent(),
                static::getChargeByFormComponent(),
            ])->columns(2),
            static::getStockAvailableFormComponent(),
            static::getDescriptionFormComponent(),

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

    public static function getDescriptionFormComponent(): Component
    {
        return Forms\Components\RichEditor::make('description');
    }

    public static function getCodeFormComponent(): Component
    {
        return Forms\Components\TextInput::make('code')->required()
            ->unique(ignoreRecord: true);
    }

    public static function getCutoffFormComponent(): Component
    {
        return Forms\Components\TimePicker::make('cutoff');
    }

    public static function getStockAvailableFormComponent(): Component
    {
        return Forms\Components\Toggle::make('stock_available')->label('Stock of all basket items must be available');
    }

    public static function getChargeByFormComponent(): Component
    {

        return Forms\Components\Group::make([
            Forms\Components\Select::make('charge_by')
                ->options([
                    'cart_total' => 'Cart Total',
                    'weight' => 'Weight',
                ]),

        ])->columns(1)->statePath('data');
    }

    public static function getDriverFormComponent(): Component
    {
        return Forms\Components\Select::make('driver')
            ->options([
                'ship-by' => 'Standard',
                'collection' => 'Collection',
            ])->label('Type')
            ->default('ship-by');
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
            Tables\Columns\TextColumn::make('driver')

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
            'index' => Pages\ListShippingMethod::route('/'),
            'edit' => Pages\EditShippingMethod::route('/{record}/edit'),
        ];
    }
}
