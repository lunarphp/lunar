<?php

namespace Lunar\Shipping\Filament\Resources;

use Awcodes\Shout\Components\Shout;
use Cartalyst\Converter\Laravel\Facades\Converter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Shipping\Filament\Resources\ShippingMethodResource\Pages;
use Lunar\Shipping\Models\Contracts\ShippingMethod;

class ShippingMethodResource extends BaseResource
{
    protected static ?string $model = ShippingMethod::class;

    protected static ?string $permission = 'shipping:manage';

    protected static ?int $navigationSort = 1;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::End;

    public static function getLabel(): string
    {
        return __('lunarpanel.shipping::shippingmethod.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel.shipping::shippingmethod.label_plural');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::shipping-methods');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel.shipping::plugin.navigation.group');
    }

    public static function getDefaultSubNavigation(): array
    {
        return [
            Pages\EditShippingMethod::class,
            Pages\ManageShippingMethodAvailability::class,
        ];
    }

    public static function getDefaultForm(Schema $schema): Schema
    {
        return $schema->components([
            Shout::make('product-customer-groups')
                ->content(
                    __('lunarpanel.shipping::shippingmethod.pages.availability.customer_groups')
                )->type('warning')->hidden(function (Model $record) {
                    return $record->customerGroups()->where('enabled', true)->count();
                }),
            Section::make()->schema(
                static::getMainFormComponents(),
            ),
        ])->columns(1);
    }

    protected static function getMainFormComponents(): array
    {
        return [
            static::getNameFormComponent(),
            Group::make([
                static::getCodeFormComponent(),
                static::getDriverFormComponent(),
            ])->columns(2),
            Group::make([
                static::getCutoffFormComponent(),
                static::getChargeByFormComponent(),
            ])->columns(2),
            static::getWeightConstraintsFormComponent(),
            static::getStockAvailableFormComponent(),
            static::getDescriptionFormComponent(),
        ];
    }

    public static function getNameFormComponent(): Component
    {
        return Forms\Components\TextInput::make('name')
            ->label(__('lunarpanel.shipping::shippingmethod.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    public static function getDescriptionFormComponent(): Component
    {
        return Forms\Components\RichEditor::make('description')
            ->label(__('lunarpanel.shipping::shippingmethod.form.description.label'));
    }

    public static function getCodeFormComponent(): Component
    {
        return Forms\Components\TextInput::make('code')
            ->label(__('lunarpanel.shipping::shippingmethod.form.code.label'))
            ->required()
            ->unique(ignoreRecord: true);
    }

    public static function getCutoffFormComponent(): Component
    {
        return Forms\Components\TimePicker::make('cutoff')
            ->label(__('lunarpanel.shipping::shippingmethod.form.cutoff.label'));
    }

    public static function getWeightConstraintsFormComponent(): Component
    {
        $weightUnits = collect(array_keys(Converter::getMeasurements()['weight'] ?? []))
            ->mapWithKeys(fn ($unit) => [$unit => $unit])
            ->all();

        return Group::make([
            Forms\Components\Select::make('weight_unit')
                ->label(__('lunarpanel.shipping::shippingmethod.form.weight_unit.label'))
                ->options($weightUnits)
                ->placeholder(__('lunarpanel.shipping::shippingmethod.form.weight_unit.placeholder')),
            Forms\Components\TextInput::make('min_weight')
                ->label(__('lunarpanel.shipping::shippingmethod.form.min_weight.label'))
                ->numeric()
                ->minValue(0)
                ->required(fn (Get $get) => filled($get('weight_unit'))),
            Forms\Components\TextInput::make('max_weight')
                ->label(__('lunarpanel.shipping::shippingmethod.form.max_weight.label'))
                ->numeric()
                ->minValue(0)
                ->required(fn (Get $get) => filled($get('weight_unit'))),
        ])->columns(3);
    }

    public static function getStockAvailableFormComponent(): Component
    {
        return Forms\Components\Toggle::make('stock_available')
            ->label(__('lunarpanel.shipping::shippingmethod.form.stock_available.label'));
    }

    public static function getChargeByFormComponent(): Component
    {
        return Group::make([
            Forms\Components\Select::make('charge_by')
                ->label(
                    __('lunarpanel.shipping::shippingmethod.form.charge_by.label')
                )
                ->options([
                    'cart_total' => __('lunarpanel.shipping::shippingmethod.form.charge_by.options.cart_total'),
                    'weight' => __('lunarpanel.shipping::shippingmethod.form.charge_by.options.weight'),
                ]),

        ])->columns(1)->statePath('data');
    }

    public static function getDriverFormComponent(): Component
    {
        return Forms\Components\Select::make('driver')
            ->label(__('lunarpanel.shipping::shippingmethod.form.driver.label'))
            ->options([
                'ship-by' => __('lunarpanel.shipping::shippingmethod.form.driver.options.ship-by'),
                'collection' => __('lunarpanel.shipping::shippingmethod.form.driver.options.collection'),
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
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function getTableColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label(
                    __('lunarpanel.shipping::shippingmethod.table.name.label')
                ),
            TextColumn::make('code')
                ->label(
                    __('lunarpanel.shipping::shippingmethod.table.code.label')
                ),
            TextColumn::make('driver')
                ->label(
                    __('lunarpanel.shipping::shippingmethod.table.driver.label')
                )->formatStateUsing(
                    fn ($state) => __("lunarpanel.shipping::shippingmethod.table.driver.options.{$state}")
                ),
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
            'index' => Pages\ListShippingMethod::route('/'),
            'edit' => Pages\EditShippingMethod::route('/{record}/edit'),
            'availability' => Pages\ManageShippingMethodAvailability::route('/{record}/availability'),
        ];
    }
}
