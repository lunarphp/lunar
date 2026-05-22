<?php

namespace Lunar\Shipping\Filament\Resources\ShippingMethodResource\Schemas;

use Filament\Forms;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Support\Concerns\CallsHooks;
use Lunar\Core\Facades\Converter;

class ShippingMethodForm
{
    use CallsHooks;

    public static function configure(Schema $schema): Schema
    {
        return self::callStaticLunarHook(
            'configureForm',
            $schema->components([
                Callout::make()
                    ->heading(__('lunarpanel.shipping::shippingmethod.pages.availability.customer_groups'))
                    ->status('warning')
                    ->hidden(fn (Model $record) => $record->customerGroups()->where('enabled', true)->count()),
                Section::make()->schema(static::getMainComponents()),
            ])->columns(1),
        );
    }

    public static function getMainComponents(): array
    {
        return [
            static::getNameComponent(),
            Group::make([
                static::getCodeComponent(),
                static::getDriverComponent(),
            ])->columns(2),
            Group::make([
                static::getChargeByComponent(),
            ])->columns(2),
            static::getWeightConstraintsComponent(),
            static::getStockAvailableComponent(),
            static::getDescriptionComponent(),
        ];
    }

    public static function getNameComponent(): Component
    {
        return Forms\Components\TextInput::make('name')
            ->label(__('lunarpanel.shipping::shippingmethod.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    public static function getDescriptionComponent(): Component
    {
        return Forms\Components\RichEditor::make('description')
            ->label(__('lunarpanel.shipping::shippingmethod.form.description.label'));
    }

    public static function getCodeComponent(): Component
    {
        return Forms\Components\TextInput::make('code')
            ->label(__('lunarpanel.shipping::shippingmethod.form.code.label'))
            ->required()
            ->unique(ignoreRecord: true);
    }

    public static function getAvailabilityScheduleComponent(): Component
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

    public static function getWeightConstraintsComponent(): Component
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

    public static function getStockAvailableComponent(): Component
    {
        return Forms\Components\Toggle::make('stock_available')
            ->label(__('lunarpanel.shipping::shippingmethod.form.stock_available.label'));
    }

    public static function getChargeByComponent(): Component
    {
        return Group::make([
            Forms\Components\Select::make('charge_by')
                ->label(__('lunarpanel.shipping::shippingmethod.form.charge_by.label'))
                ->options([
                    'cart_total' => __('lunarpanel.shipping::shippingmethod.form.charge_by.options.cart_total'),
                    'weight' => __('lunarpanel.shipping::shippingmethod.form.charge_by.options.weight'),
                ])
                ->required(),
        ])->columns(1)->statePath('data');
    }

    public static function getDriverComponent(): Component
    {
        return Forms\Components\Select::make('driver')
            ->label(__('lunarpanel.shipping::shippingmethod.form.driver.label'))
            ->options([
                'ship-by' => __('lunarpanel.shipping::shippingmethod.form.driver.options.ship-by'),
                'collection' => __('lunarpanel.shipping::shippingmethod.form.driver.options.collection'),
            ])
            ->default('ship-by')
            ->required();
    }
}
