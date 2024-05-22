<?php

namespace Lunar\Admin\Filament\Resources;

use Awcodes\FilamentBadgeableColumn\Components\Badge;
use Awcodes\FilamentBadgeableColumn\Components\BadgeableColumn;
use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\CustomerGroupResource\Pages;
use Lunar\Admin\Support\Forms\Components\Attributes;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Models\CustomerGroup;

class CustomerGroupResource extends BaseResource
{
    protected static ?string $permission = 'settings:core';

    protected static ?string $model = CustomerGroup::class;

    protected static ?int $navigationSort = 1;

    public static function getLabel(): string
    {
        return __('lunarpanel::customergroup.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::customergroup.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::customer-groups');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel::global.sections.settings');
    }

    protected static function getMainFormComponents(): array
    {
        return [
            static::getNameFormComponent(),
            static::getHandleFormComponent(),
            static::getDefaultFormComponent(),
            static::getAttributeDataFormComponent(),
        ];
    }

    protected static function getNameFormComponent(): Component
    {
        return Forms\Components\TextInput::make('name')
            ->label(__('lunarpanel::customergroup.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected static function getHandleFormComponent(): Component
    {
        return Forms\Components\TextInput::make('handle')
            ->label(__('lunarpanel::customergroup.form.handle.label'))
            ->required()
            ->unique(ignoreRecord: true)
            ->minLength(3)
            ->maxLength(255);
    }

    protected static function getDefaultFormComponent(): Component
    {
        return Forms\Components\Toggle::make('default')
            ->label(__('lunarpanel::customergroup.form.default.label'));
    }

    protected static function getAttributeDataFormComponent(): Component
    {
        return Attributes::make()->statePath('attribute_data');
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
            BadgeableColumn::make('name')
                ->separator('')
                ->suffixBadges([
                    Badge::make('default')
                        ->label(__('lunarpanel::customergroup.table.default.label'))
                        ->color('gray')
                        ->visible(fn(Model $record) => $record->default),
                ])
                ->label(__('lunarpanel::customergroup.table.name.label')),
            Tables\Columns\TextColumn::make('handle')
                ->label(__('lunarpanel::customergroup.table.handle.label')),
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
            'index' => Pages\ListCustomerGroups::route('/'),
            'create' => Pages\CreateCustomerGroup::route('/create'),
            'edit' => Pages\EditCustomerGroup::route('/{record}/edit'),
        ];
    }
}
