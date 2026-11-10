<?php

namespace Lunar\Shipping\Filament\Resources;

use Awcodes\Shout\Components\Shout;
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
use Lunar\Facades\Converter;
use Lunar\Shipping\Facades\Shipping;
use Lunar\Shipping\Filament\Resources\ShippingMethodResource\Pages;
use Lunar\Shipping\Filament\Resources\ShippingMethodResource\Widgets\AvailabilityScheduleWidget;
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

    public static function getAvailabilityScheduleFormComponent(): Component
    {
        $days = [
            1 => __('lunarpanel.shipping::shippingmethod.form.schedule.days.monday'),
            2 => __('lunarpanel.shipping::shippingmethod.form.schedule.days.tuesday'),
            3 => __('lunarpanel.shipping::shippingmethod.form.schedule.days.wednesday'),
            4 => __('lunarpanel.shipping::shippingmethod.form.schedule.days.thursday'),
            5 => __('lunarpanel.shipping::shippingmethod.form.schedule.days.friday'),
            6 => __('lunarpanel.shipping::shippingmethod.form.schedule.days.saturday'),
            7 => __('lunarpanel.shipping::shippingmethod.form.schedule.days.sunday'),
        ];

        $rows = collect($days)->map(fn ($label, $day) => Group::make([
            Forms\Components\Checkbox::make('enabled')
                ->label($label)
                ->live()
                ->columnSpan(1),
            Forms\Components\TimePicker::make('from')
                ->label(__('lunarpanel.shipping::shippingmethod.form.schedule.from.label'))
                ->seconds(false)
                ->disabled(fn (Get $get) => ! $get('enabled'))
                ->columnSpan(1),
            Forms\Components\TimePicker::make('to')
                ->label(__('lunarpanel.shipping::shippingmethod.form.schedule.to.label'))
                ->seconds(false)
                ->disabled(fn (Get $get) => ! $get('enabled'))
                ->rules(fn (Get $get): array => filled($get('from')) ? ['after:'.$get('from')] : [])
                ->validationMessages([
                    'after' => __('lunarpanel.shipping::shippingmethod.form.schedule.to.validation.after'),
                ])
                ->columnSpan(1),
        ])->statePath((string) $day)->columns(3)
        )->values()->toArray();

        return Section::make(__('lunarpanel.shipping::shippingmethod.form.schedule.label'))
            ->schema($rows)
            ->statePath('data.schedule')
            ->collapsed()
            ->collapsible();
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
                ->live()
                ->required(fn (Get $get) => filled($get('weight_unit'))),
            Forms\Components\TextInput::make('max_weight')
                ->label(__('lunarpanel.shipping::shippingmethod.form.max_weight.label'))
                ->numeric()
                ->minValue(0)
                ->required(fn (Get $get) => filled($get('weight_unit')))
                ->rules(fn (Get $get) => filled($get('min_weight')) ? ['gt:'.$get('min_weight')] : []),
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
                ])
                ->required(),
        ])->columns(1)->statePath('data');
    }

    public static function getDriverFormComponent(): Component
    {
        return Forms\Components\Select::make('driver')
            ->label(__('lunarpanel.shipping::shippingmethod.form.driver.label'))
            ->options(static::getDriverOptions())
            ->default('ship-by')
            ->required();
    }

    protected static function getDriverOptions(): array
    {
        return Shipping::getSupportedDrivers()->keys()->mapWithKeys(
            fn ($driver) => [$driver => __("lunarpanel.shipping::shippingmethod.form.driver.options.{$driver}")]
        )->all();
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

    public static function getWidgets(): array
    {
        return [
            AvailabilityScheduleWidget::class,
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
