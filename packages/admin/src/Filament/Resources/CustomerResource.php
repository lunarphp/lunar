<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\CustomerResource\Pages\CreateCustomer;
use Lunar\Admin\Filament\Resources\CustomerResource\Pages\EditCustomer;
use Lunar\Admin\Filament\Resources\CustomerResource\Pages\ListCustomers;
use Lunar\Admin\Filament\Resources\CustomerResource\Pages\ViewCustomer;
use Lunar\Admin\Filament\Resources\CustomerResource\RelationManagers\AddressRelationManager;
use Lunar\Admin\Filament\Resources\CustomerResource\RelationManagers\OrdersRelationManager;
use Lunar\Admin\Filament\Resources\CustomerResource\RelationManagers\UserRelationManager;
use Lunar\Admin\Filament\Resources\CustomerResource\Widgets\CustomerStatsOverviewWidget;
use Lunar\Admin\Support\Forms\Components\Attributes;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Models\Contracts\Customer as CustomerContract;

class CustomerResource extends BaseResource
{
    protected static ?string $permission = 'sales:manage-customers';

    protected static ?string $model = CustomerContract::class;

    protected static ?int $navigationSort = 2;

    protected static int $globalSearchResultsLimit = 5;

    public static function getWidgets(): array
    {
        return [
            CustomerStatsOverviewWidget::class,
        ];
    }

    public static function getDefaultForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make([
                    Section::make()
                        ->id('details')
                        ->schema(
                            static::getMainFormComponents()
                        ),
                    static::getAttributeDataFormComponent(),
                ])->columnSpan(4),
                Section::make()
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
            Group::make()->schema([
                static::getTitleFormComponent()->columnSpan(1),
                static::getFirstNameFormComponent()->columnSpan(2),
                static::getLastNameFormComponent()->columnSpan(2),
            ])->columns(5),
            static::getCompanyNameFormComponent(),
            Group::make()->schema([
                static::getAccountRefFormComponent(),
                static::getTaxIdFormComponent(),
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
        return TextInput::make('title')
            ->label(__('lunarpanel::customer.form.title.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected static function getAttributeDataFormComponent(): Component
    {
        return Attributes::make();
    }

    protected static function getFirstNameFormComponent(): Component
    {
        return TextInput::make('first_name')
            ->label(__('lunarpanel::customer.form.first_name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected static function getLastNameFormComponent(): Component
    {
        return TextInput::make('last_name')
            ->label(__('lunarpanel::customer.form.last_name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected static function getCompanyNameFormComponent(): Component
    {
        return TextInput::make('company_name')
            ->label(__('lunarpanel::customer.form.company_name.label'))
            ->nullable()
            ->maxLength(255)
            ->autofocus();
    }

    protected static function getAccountRefFormComponent(): Component
    {
        return TextInput::make('account_ref')
            ->label(__('lunarpanel::customer.form.account_ref.label'))
            ->nullable()
            ->maxLength(255);
    }

    protected static function getTaxIdFormComponent(): Component
    {
        return TextInput::make('tax_identifier')
            ->label(__('lunarpanel::customer.form.tax_identifier.label'))
            ->nullable()
            ->maxLength(255);
    }

    protected static function getCustomerGroupsFormComponent(): Component
    {
        return CheckboxList::make('customerGroups')
            ->label(__('lunarpanel::customer.form.customer_groups.label'))
            ->relationship(
                name: 'customerGroups',
                titleAttribute: 'name',
                modifyQueryUsing: fn (Builder $query) => $query->distinct(
                    ['id', 'name', 'handle', 'default']
                )
            );
    }

    protected static function getDefaultTable(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('first_name')
                    ->label(__('lunarpanel::customer.table.first_name.label'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('last_name')
                    ->label(__('lunarpanel::customer.table.last_name.label'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('company_name')
                    ->label(__('lunarpanel::customer.table.company_name.label'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('tax_identifier')
                    ->label(__('lunarpanel::customer.table.tax_identifier.label'))
                    ->sortable(),
                TextColumn::make('account_ref')
                    ->label(__('lunarpanel::customer.table.account_reference.label'))
                    ->sortable(),
                TextColumn::make('customerGroups.name')
                    ->label(__('lunarpanel::customergroup.label'))
                    ->badge()
                    ->limitList(1)
                    ->tooltip(function (TextColumn $column, Model $record): ?string {
                        if ($record->customerGroups->count() <= $column->getListLimit()) {
                            return null;
                        }

                        return $record->customerGroups
                            ->map(fn ($customerGroup) => $customerGroup->name)
                            ->implode(', ');
                    }),
            ])
            ->filters([
                SelectFilter::make('customer_group')
                    ->label(__('lunarpanel::customergroup.label'))
                    ->relationship(
                        name: 'customerGroups',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query->distinct(
                            ['id', 'name', 'handle', 'default']
                        )
                    )
                    ->multiple()
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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

    public static function getDefaultPages(): array
    {
        return [
            'index' => ListCustomers::route('/'),
            'create' => CreateCustomer::route('/create'),
            'edit' => EditCustomer::route('/{record}/edit'),
            'view' => ViewCustomer::route('/{record}'),
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
            'tax_identifier',
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
