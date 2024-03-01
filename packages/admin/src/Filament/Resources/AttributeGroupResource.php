<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Form;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Resources\AttributeGroupResource\Pages;
use Lunar\Admin\Filament\Resources\AttributeGroupResource\RelationManagers;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Models\AttributeGroup;

class AttributeGroupResource extends BaseResource
{
    protected static ?string $permission = 'settings:manage-attributes';

    protected static ?string $model = AttributeGroup::class;

    protected static ?int $navigationSort = 1;

    public static function getLabel(): string
    {
        return __('lunarpanel::attributegroup.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::attributegroup.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::attributes');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel::global.sections.settings');
    }

    public static function getDefaultForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()->schema(
                    static::getMainFormComponents()
                ),
            ]);
    }

    protected static function getMainFormComponents(): array
    {
        return [
            static::getAttributableTypeFormComponent(),
            static::getNameFormComponent(),
            static::getHandleFormComponent(),
            static::getPositionFormComponent(),
        ];
    }

    protected static function getAttributableTypeFormComponent(): Component
    {
        return Forms\Components\TextInput::make('attributable_type')
            ->label(__('lunarpanel::attributegroup.form.attributable_type.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected static function getNameFormComponent(): Component
    {
        return \Lunar\Admin\Support\Forms\Components\TranslatedText::make('name')
            ->label(__('lunarpanel::attributegroup.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected static function getHandleFormComponent(): Component
    {
        return Forms\Components\TextInput::make('handle')
            ->label(__('lunarpanel::attributegroup.form.handle.label'))
            ->required()
            ->maxLength(255);
    }

    protected static function getPositionFormComponent(): Component
    {
        return Forms\Components\TextInput::make('position')
            ->label(__('lunarpanel::attributegroup.form.position.label'))
            ->numeric()
            ->minValue(1)
            ->maxValue(100)
            ->required();
    }

    public static function getDefaultTable(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('attributable_type')
                    ->label(__('lunarpanel::attributegroup.table.attributable_type.label')),
                Tables\Columns\TextColumn::make('name.en')  // TODO: Need to determine correct way to localise, maybe custom column type?
                    ->label(__('lunarpanel::attributegroup.table.name.label')),
                Tables\Columns\TextColumn::make('handle')
                    ->label(__('lunarpanel::attributegroup.table.handle.label')),
                Tables\Columns\TextColumn::make('position')
                    ->label(__('lunarpanel::attributegroup.table.position.label'))
                    ->sortable(),
            ])
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
            ])
            ->defaultSort('position', 'asc')
            ->reorderable('position');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AttributesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttributeGroups::route('/'),
            'create' => Pages\CreateAttributeGroup::route('/create'),
            'edit' => Pages\EditAttributeGroup::route('/{record}/edit'),
        ];
    }
}
