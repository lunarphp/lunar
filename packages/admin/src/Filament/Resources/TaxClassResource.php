<?php

namespace Lunar\Admin\Filament\Resources;

use Awcodes\BadgeableColumn\Components\Badge;
use Awcodes\BadgeableColumn\Components\BadgeableColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Clusters\Taxes;
use Lunar\Admin\Filament\Resources\TaxClassResource\Pages\CreateTaxClass;
use Lunar\Admin\Filament\Resources\TaxClassResource\Pages\EditTaxClass;
use Lunar\Admin\Filament\Resources\TaxClassResource\Pages\ListTaxClasses;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Models\Contracts\TaxClass as TaxClassContract;

class TaxClassResource extends BaseResource
{
    protected static ?string $cluster = Taxes::class;

    protected static ?string $permission = 'settings:core';

    protected static ?string $model = TaxClassContract::class;

    protected static ?int $navigationSort = 1;

    public static function getLabel(): string
    {
        return __('lunarpanel::taxclass.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::taxclass.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::tax');
    }

    protected static function getMainFormComponents(): array
    {
        return [
            Section::make()->schema([
                static::getNameFormComponent(),
                static::getDefaultFormComponent(),
            ]),
        ];
    }

    protected static function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('lunarpanel::taxclass.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected static function getDefaultFormComponent(): Component
    {
        return Toggle::make('default')
            ->label(__('lunarpanel::taxzone.form.default.label'));
    }

    public static function getDefaultTable(Table $table): Table
    {
        return $table
            ->columns(static::getTableColumns())
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
                        ->label(__('lunarpanel::taxclass.table.default.label'))
                        ->color('gray')
                        ->visible(fn (Model $record) => $record->default),
                ])
                ->label(__('lunarpanel::taxclass.table.name.label')),
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
            'index' => ListTaxClasses::route('/'),
            'create' => CreateTaxClass::route('/create'),
            'edit' => EditTaxClass::route('/{record}/edit'),
        ];
    }
}
