<?php

namespace Lunar\Filament\Schemas\Discount;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Lunar\Core\DiscountTypes\AmountOff;
use Lunar\Core\DiscountTypes\BuyXGetY;
use Lunar\Core\Facades\Discounts;
use Lunar\Core\Models\Currency;
use Lunar\Filament\Contracts\DiscountFormType;
use Lunar\Filament\Support\Concerns\CallsHooks;

class DiscountForm
{
    use CallsHooks;

    public static function configure(Schema $schema): Schema
    {
        $discountSchemas = Discounts::getTypes()->map(function ($discount) {
            if (! $discount instanceof DiscountFormType) {
                return;
            }

            return Section::make(Str::slug(get_class($discount)))
                ->heading($discount->getName())
                ->visible(
                    fn (Get $get) => $get('type') == get_class($discount)
                )->schema($discount->lunarPanelSchema());
        })->filter();

        return self::callStaticLunarHook(
            'configureForm',
            $schema->components([
                Section::make('')->schema(static::getMainComponents()),
                Section::make('conditions')
                    ->schema(static::getConditionsComponents())
                    ->heading(__('lunar-filament::discount.form.conditions.heading')),
                Section::make('buy_x_get_y')
                    ->heading(__('lunar-filament::discount.form.buy_x_get_y.heading'))
                    ->visible(fn (Get $get) => $get('type') == BuyXGetY::class)
                    ->schema(static::getBuyXGetYComponents()),
                Section::make('amount_off')
                    ->heading(__('lunar-filament::discount.form.amount_off.heading'))
                    ->visible(fn (Get $get) => $get('type') == AmountOff::class)
                    ->schema(static::getAmountOffComponents()),
                ...$discountSchemas,
            ]),
        );
    }

    public static function getMainComponents(): array
    {
        return [
            Group::make([
                static::getNameComponent(),
                static::getHandleComponent(),
            ])->columns(2),
            Group::make([
                static::getStartsAtComponent(),
                static::getEndsAtComponent(),
            ])->columns(2),
            Group::make([
                static::getPriorityComponent(),
                static::getDiscountTypeComponent(),
            ])->columns(2),
            static::getStopComponent(),
            static::getPublicIdComponent(),
        ];
    }

    public static function getPublicIdComponent(): Component
    {
        return TextEntry::make('public_id')
            ->label(__('lunar-filament::components.public_id.label'))
            ->copyable()
            ->visible(fn ($record): bool => (bool) $record);
    }

    public static function getConditionsComponents(): array
    {
        return [
            Group::make([
                static::getCouponComponent(),
                static::getMaxUsesComponent(),
                static::getMaxUsesPerUserComponent(),
            ])->columns(3),
            Fieldset::make()
                ->schema(static::getMinimumCartAmountsComponents())
                ->label(__('lunar-filament::discount.form.minimum_cart_amount.label')),
        ];
    }

    public static function getNameComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('lunar-filament::discount.form.name.label'))
            ->live(onBlur: true)
            ->afterStateUpdated(function (string $operation, $state, Set $set) {
                if ($operation !== 'create') {
                    return;
                }
                $set('handle', Str::slug($state));
            })
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    public static function getHandleComponent(): Component
    {
        return TextInput::make('handle')
            ->label(__('lunar-filament::discount.form.handle.label'))
            ->required()
            ->unique(ignoreRecord: true)
            ->live(onBlur: true)
            ->afterStateUpdated(function (string $operation, $state, Set $set) {
                if ($operation !== 'create') {
                    return;
                }

                $set('handle', Str::snake(Str::lower($state)));
            })
            ->maxLength(255)
            ->autofocus();
    }

    public static function getStartsAtComponent(): Component
    {
        return DateTimePicker::make('starts_at')
            ->label(__('lunar-filament::discount.form.starts_at.label'))
            ->required()
            ->before(fn (Get $get) => $get('ends_at'));
    }

    public static function getEndsAtComponent(): Component
    {
        return DateTimePicker::make('ends_at')
            ->label(__('lunar-filament::discount.form.ends_at.label'));
    }

    public static function getPriorityComponent(): Component
    {
        return TextInput::make('priority')
            ->label(__('lunar-filament::discount.form.priority.label'))
            ->helperText(__('lunar-filament::discount.form.priority.helper_text'))
            ->numeric()
            ->minValue(1)
            ->maxValue(100)
            ->default(1);
    }

    public static function getStopComponent(): Component
    {
        return Toggle::make('stop')
            ->label(__('lunar-filament::discount.form.stop.label'))
            ->helperText(__('lunar-filament::discount.form.stop.helper_text'));
    }

    public static function getCouponComponent(): Component
    {
        return TextInput::make('coupon')
            ->label(__('lunar-filament::discount.form.coupon.label'))
            ->helperText(__('lunar-filament::discount.form.coupon.helper_text'))
            ->unique(ignoreRecord: true);
    }

    public static function getMaxUsesComponent(): Component
    {
        return TextInput::make('max_uses')
            ->label(__('lunar-filament::discount.form.max_uses.label'))
            ->helperText(__('lunar-filament::discount.form.max_uses.helper_text'));
    }

    public static function getMaxUsesPerUserComponent(): Component
    {
        return TextInput::make('max_uses_per_user')
            ->label(__('lunar-filament::discount.form.max_uses_per_user.label'))
            ->helperText(__('lunar-filament::discount.form.max_uses_per_user.helper_text'));
    }

    public static function getMinimumCartAmountsComponents(): array
    {
        $currencies = Currency::enabled()->get();
        $inputs = [];

        foreach ($currencies as $currency) {
            $inputs[] = TextInput::make('data.min_prices.'.$currency->code)
                ->label($currency->code)
                ->afterStateHydrated(function (TextInput $component, $state) {
                    $currencyCode = last(explode('.', $component->getStatePath()));
                    $currency = Currency::whereCode($currencyCode)->first();

                    if ($currency) {
                        $component->state($state / $currency->factor);
                    }
                });
        }

        return $inputs;
    }

    public static function getDiscountTypeComponent(): Component
    {
        return Select::make('type')
            ->options(
                Discounts::getTypes()->mapWithKeys(
                    fn ($type) => [get_class($type) => $type->getName()]
                )
            )
            ->required()
            ->live();
    }

    public static function getAmountOffComponents(): array
    {
        $currencies = Currency::get();

        $currencyInputs = [];

        foreach ($currencies as $currency) {
            $currencyInputs[] = TextInput::make('data.fixed_values.'.$currency->code)
                ->label($currency->name)
                ->afterStateHydrated(function (TextInput $component, $state) use ($currencies) {
                    $currencyCode = last(explode('.', $component->getStatePath()));
                    $currency = $currencies->first(
                        fn ($currency) => $currency->code == $currencyCode
                    );

                    if ($currency) {
                        $component->state($state / $currency->factor);
                    }
                });
        }

        return [
            Toggle::make('data.fixed_value')
                ->label(__('lunar-filament::discount.form.fixed_value.label'))
                ->live(),
            TextInput::make('data.percentage')
                ->label(__('lunar-filament::discount.form.percentage.label'))
                ->visible(fn (Get $get) => ! $get('data.fixed_value'))
                ->numeric(),
            Group::make($currencyInputs)
                ->visible(fn (Get $get) => (bool) $get('data.fixed_value'))
                ->columns(3),
        ];
    }

    public static function getBuyXGetYComponents(): array
    {
        return [
            TextInput::make('data.min_qty')
                ->label(__('lunar-filament::discount.form.min_qty.label'))
                ->helperText(__('lunar-filament::discount.form.min_qty.helper_text'))
                ->numeric(),
            Group::make([
                TextInput::make('data.reward_qty')
                    ->label(__('lunar-filament::discount.form.reward_qty.label'))
                    ->helperText(__('lunar-filament::discount.form.reward_qty.helper_text'))
                    ->numeric(),
                TextInput::make('data.max_reward_qty')
                    ->label(__('lunar-filament::discount.form.max_reward_qty.label'))
                    ->helperText(__('lunar-filament::discount.form.max_reward_qty.helper_text'))
                    ->numeric(),
            ])->columns(2),
            Toggle::make('data.automatically_add_rewards')
                ->label(__('lunar-filament::discount.form.automatic_rewards.label'))
                ->helperText(__('lunar-filament::discount.form.automatic_rewards.helper_text')),
        ];
    }
}
