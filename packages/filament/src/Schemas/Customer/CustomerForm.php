<?php

namespace Lunar\Filament\Schemas\Customer;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Lunar\Filament\Forms\Components\Attributes;
use Lunar\Filament\Support\Concerns\CallsHooks;

class CustomerForm
{
    use CallsHooks;

    public static function configure(Schema $schema): Schema
    {
        return self::callStaticLunarHook(
            'configureForm',
            $schema
                ->components([
                    Group::make([
                        Section::make()
                            ->id('details')
                            ->schema(static::getMainComponents()),
                        static::getAttributeDataComponent(),
                    ])->columnSpan(4),
                    Section::make()
                        ->id('details')
                        ->schema(static::getSideComponents())
                        ->columnSpan(2),
                ])
                ->columns(6),
        );
    }

    public static function getMainComponents(): array
    {
        return [
            Group::make()->schema([
                static::getTitleComponent()->columnSpan(1),
                static::getFirstNameComponent()->columnSpan(2),
                static::getLastNameComponent()->columnSpan(2),
            ])->columns(5),
            static::getCompanyNameComponent(),
            Group::make()->schema([
                static::getAccountRefComponent(),
                static::getTaxIdComponent(),
            ])->columns(2),
        ];
    }

    public static function getSideComponents(): array
    {
        return [
            static::getCustomerGroupsComponent(),
        ];
    }

    public static function getTitleComponent(): Component
    {
        return TextInput::make('title')
            ->label(__('lunar-filament::customer.form.title.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    public static function getAttributeDataComponent(): Component
    {
        return Attributes::make();
    }

    public static function getFirstNameComponent(): Component
    {
        return TextInput::make('first_name')
            ->label(__('lunar-filament::customer.form.first_name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    public static function getLastNameComponent(): Component
    {
        return TextInput::make('last_name')
            ->label(__('lunar-filament::customer.form.last_name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    public static function getCompanyNameComponent(): Component
    {
        return TextInput::make('company_name')
            ->label(__('lunar-filament::customer.form.company_name.label'))
            ->nullable()
            ->maxLength(255)
            ->autofocus();
    }

    public static function getAccountRefComponent(): Component
    {
        return TextInput::make('account_ref')
            ->label(__('lunar-filament::customer.form.account_ref.label'))
            ->nullable()
            ->maxLength(255);
    }

    public static function getTaxIdComponent(): Component
    {
        return TextInput::make('tax_identifier')
            ->label(__('lunar-filament::customer.form.tax_identifier.label'))
            ->nullable()
            ->maxLength(255);
    }

    public static function getCustomerGroupsComponent(): Component
    {
        return CheckboxList::make('customerGroups')
            ->label(__('lunar-filament::customer.form.customer_groups.label'))
            ->relationship(
                name: 'customerGroups',
                titleAttribute: 'name',
                modifyQueryUsing: fn (Builder $query) => $query->distinct(
                    ['id', 'name', 'handle', 'default']
                )
            );
    }
}
