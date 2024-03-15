<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\CustomerResource\Pages;
use Lunar\Admin\Filament\Resources\CustomerResource\RelationManagers\AddressRelationManager;
use Lunar\Admin\Filament\Resources\CustomerResource\RelationManagers\OrdersRelationManager;
use Lunar\Admin\Filament\Resources\CustomerResource\RelationManagers\UserRelationManager;
use Lunar\Admin\Filament\Resources\CustomerResource\Widgets\CustomerStatsOverviewWidget;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Models\Customer;

class CustomerResource extends BaseResource
{
    protected static ?string $permission = 'sales:manage-customers';

    protected static ?string $model = Customer::class;

    protected static ?int $navigationSort = 2;

    protected static int $globalSearchResultsLimit = 5;

    public static function getWidgets(): array
    {
        return [
            CustomerStatsOverviewWidget::class,
        ];
    }

    public static function getDefaultForm(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make([
                    Forms\Components\Section::make()
                        ->id('details')
                        ->schema(
                            static::getMainFormComponents()
                        ),
                    static::getAttributeDataFormComponent(),
                ])->columnSpan(4),
                Forms\Components\Section::make()
                    ->id('details')
                    ->schema(
                        static::getSideFormComponents()
                    )->columnSpan(2),
            ])->columns(6);
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::customers');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel::global.sections.sales');
    }

    public static function getLabel(): string
    {
        return __('lunarpanel::customer.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::customer.plural_label');
    }

    protected static function getMainFormComponents(): array
    {
        return [
            Forms\Components\Group::make()->schema([
                static::getTitleFormComponent()->columnSpan(1),
                static::getFirstNameFormComponent()->columnSpan(2),
                static::getLastNameFormComponent()->columnSpan(2),
            ])->columns(5),
            static::getCompanyNameFormComponent(),
            Forms\Components\Group::make()->schema([
                static::getAccountRefFormComponent(),
                static::getVatNoFormComponent(),
            ])->columns(2),
        ];
    }

    protected static function getSideFormComponents(): array
    {
        return [
            static::getCustomerGroupsFormComponent(),
        ];
    }

    protected static function getTitleFormComponent(): Component
    {
        return Forms\Components\TextInput::make('title')
            ->label(__('lunarpanel::customer.form.title.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected static function getAttributeDataFormComponent(): Component
    {
        return \Lunar\Admin\Support\Forms\Components\Attributes::make()->statePath('attribute_data');
    }

    protected static function getFirstNameFormComponent(): Component
    {
        return Forms\Components\TextInput::make('first_name')
            ->label(__('lunarpanel::customer.form.first_name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected static function getLastNameFormComponent(): Component
    {
        return Forms\Components\TextInput::make('last_name')
            ->label(__('lunarpanel::customer.form.last_name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected static function getCompanyNameFormComponent(): Component
    {
        return Forms\Components\TextInput::make('company_name')
            ->label(__('lunarpanel::customer.form.company_name.label'))
            ->nullable()
            ->maxLength(255)
            ->autofocus();
    }

    protected static function getAccountRefFormComponent(): Component
    {
        return Forms\Components\TextInput::make('account_ref')
            ->label(__('lunarpanel::customer.form.account_ref.label'))
            ->nullable()
            ->maxLength(255);
    }

    protected static function getVatNoFormComponent(): Component
    {
        return Forms\Components\TextInput::make('vat_no')
            ->label(__('lunarpanel::customer.form.vat_no.label'))
            ->nullable()
            ->maxLength(255);
    }

    protected static function getCustomerGroupsFormComponent(): Component
    {
        return Forms\Components\CheckboxList::make('customerGroups')
            ->label(__('lunarpanel::customer.form.customer_groups.label'))
            ->relationship(name: 'customerGroups', titleAttribute: 'name');
    }

    protected static function getDefaultTable(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    ->label(__('lunarpanel::customer.table.first_name.label'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->label(__('lunarpanel::customer.table.last_name.label'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('company_name')
                    ->label(__('lunarpanel::customer.table.company_name.label'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('vat_no')
                    ->label(__('lunarpanel::customer.table.vat_no.label'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('account_ref')
                    ->label(__('lunarpanel::customer.table.account_reference.label'))
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->selectCurrentPageOnly();
    }

    public static function getDefaultRelations(): array
    {
        return [
            OrdersRelationManager::class,
            AddressRelationManager::class,
            UserRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
            'view' => Pages\ViewCustomer::route('/{record}'),
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return $record->company_name ?: $record->fullName;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'first_name',
            'last_name',
            'company_name',
            'account_ref',
            'vat_no',
            'users.name',
            'users.email',
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with([
            'users',
        ]);
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var Customer $record */
        $details = [
            __('lunarpanel::customer.table.full_name.label') => $record->fullName,
            __('lunarpanel::customer.table.title.label') => $record->title,
        ];

        if ($record->account_ref) {
            $details[__('lunarpanel::customer.table.account_reference.label')] = $record->account_ref;
        }

        if ($record->users() && $record->users()->count() >= 1) {
            $details[__('lunarpanel::user.table.email.label')] = $record->users()->first()->email;
        }

        return $details;
    }
}
