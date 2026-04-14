<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Lunar\Admin\Filament\Resources\AttributeGroupResource\Pages\CreateAttributeGroup;
use Lunar\Admin\Filament\Resources\AttributeGroupResource\Pages\EditAttributeGroup;
use Lunar\Admin\Filament\Resources\AttributeGroupResource\Pages\ListAttributeGroups;
use Lunar\Admin\Filament\Resources\AttributeGroupResource\RelationManagers\AttributesRelationManager;
use Lunar\Admin\Support\Forms\Components\TranslatedText;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Admin\Support\Tables\Columns\TranslatedTextColumn;
use Lunar\Facades\AttributeManifest;
use Lunar\Facades\ModelManifest;
use Lunar\Models\Contracts\AttributeGroup as AttributeGroupContract;
use Lunar\Models\Language;

class AttributeGroupResource extends BaseResource
{
    protected static ?string $permission = 'settings:manage-attributes';

    protected static ?string $model = AttributeGroupContract::class;

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

    public static function getDefaultForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->schema(
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
        return Select::make('attributable_type')
            ->label(__('lunarpanel::attributegroup.form.attributable_type.label'))
            ->options(function () {
                return AttributeManifest::getTypes()->mapWithKeys(
                    fn ($type) => [
                        ModelManifest::getMorphMapKey($type) => class_basename($type),
                    ]
                );
            })
            ->required()
            ->autofocus();
    }

    protected static function getNameFormComponent(): Component
    {
        return TranslatedText::make('name')
            ->label(__('lunarpanel::attributegroup.form.name.label'))
            ->required()
            ->maxLength(255)
            ->afterStateUpdated(function (string $operation, $state, Set $set) {
                if ($operation !== 'create') {
                    return;
                }
                $set('handle', Str::slug($state[Language::getDefault()->code]));
            })
            ->live(onBlur: true)
            ->autofocus();
    }

    protected static function getHandleFormComponent(): Component
    {
        return TextInput::make('handle')
            ->label(__('lunarpanel::attributegroup.form.handle.label'))
            ->live(onBlur: true)
            ->afterStateUpdated(function (string $operation, $state, Set $set) {
                if ($operation !== 'create') {
                    return;
                }

                $set('handle', Str::snake(Str::lower($state)));
            })
            ->required()
            ->maxLength(255);
    }

    protected static function getPositionFormComponent(): Component
    {
        return TextInput::make('position')
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
                TextColumn::make('attributable_type')
                    ->label(__('lunarpanel::attributegroup.table.attributable_type.label')),
                TranslatedTextColumn::make('name')
                    ->label(__('lunarpanel::attributegroup.table.name.label')),
                TextColumn::make('handle')
                    ->label(__('lunarpanel::attributegroup.table.handle.label')),
                TextColumn::make('position')
                    ->label(__('lunarpanel::attributegroup.table.position.label'))
                    ->sortable(),
            ])
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
            ])
            ->defaultSort('position', 'asc')
            ->reorderable('position');
    }

    public static function getRelations(): array
    {
        return [
            AttributesRelationManager::class,
        ];
    }

    public static function getDefaultPages(): array
    {
        return [
            'index' => ListAttributeGroups::route('/'),
            'create' => CreateAttributeGroup::route('/create'),
            'edit' => EditAttributeGroup::route('/{record}/edit'),
        ];
    }
}
