<?php

namespace Lunar\Admin\Filament\Resources\ProductResource\Pages;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Lunar\Admin\Events\ProductAssociationsUpdated;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Support\Pages\BaseManageRelatedRecords;
use Lunar\Core\Models\ProductAssociation;
use Lunar\Filament\Forms\Components\ProductSelect;

class ManageProductAssociations extends BaseManageRelatedRecords
{
    protected static string $resource = ProductResource::class;

    protected static string $relationship = 'associations';

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::product-associations');
    }

    public function getTitle(): string
    {
        return __('lunarpanel::product.pages.associations.label');
    }

    public static function getNavigationLabel(): string
    {
        return __('lunarpanel::product.pages.associations.label');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                ProductSelect::make('product_target_id')
                    ->required(),
                Select::make('type')
                    ->required()
                    ->options(ProductAssociation::getTypes()),
            ]);
    }

    public function table(Table $table): Table
    {
        return parent::table($table);
    }

    protected function getDefaultTable(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->inverseRelationship('parent')
            ->columns([
                TextColumn::make('target_name')
                    ->state(fn (ProductAssociation $record): ?string => $record->target?->translate('name'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $column, ProductAssociation $record): ?string {
                        $name = $record->target?->translate('name');

                        if ($name === null || strlen($name) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        return $name;
                    })
                    ->label(__('lunarpanel::product.table.name.label')),
                TextColumn::make('target.variants.sku')
                    ->label('SKU'),
                TextColumn::make('type')->formatStateUsing(function ($state) {
                    $enum = config('lunar.products.association_types_enum', \Lunar\Core\Enums\ProductAssociation::class);

                    return $enum::tryFrom($state)?->label() ?: $state;
                }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()->after(
                    fn () => ProductAssociationsUpdated::dispatch(
                        $this->getOwnerRecord()
                    )
                ),
            ])
            ->recordActions([
                DeleteAction::make()->after(
                    fn () => ProductAssociationsUpdated::dispatch(
                        $this->getOwnerRecord()
                    )
                ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->after(
                        fn () => ProductAssociationsUpdated::dispatch(
                            $this->getOwnerRecord()
                        )
                    ),
                ]),
            ]);
    }
}
