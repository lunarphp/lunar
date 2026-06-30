<?php

namespace Lunar\Admin\Filament\Resources\CollectionResource\Pages;

use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Lunar\Admin\Filament\Resources\CollectionResource;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Support\Pages\BaseManageRelatedRecords;
use Lunar\Core\Models\Product;
use Lunar\Filament\Forms\Components\ProductSelect;

class ManageCollectionProducts extends BaseManageRelatedRecords
{
    protected static string $resource = CollectionResource::class;

    protected static string $relationship = 'products';

    public ?string $tableSortColumn = 'position';

    public function getTitle(): string
    {
        return __('lunarpanel::collection.pages.products.label');
    }

    public function getBreadcrumbs(): array
    {
        $crumbs = static::getResource()::getCollectionBreadcrumbs($this->getRecord());

        $crumbs[] = $this->getBreadcrumb();

        return $crumbs;
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::products');
    }

    public static function getNavigationLabel(): string
    {
        return __('lunarpanel::collection.pages.products.label');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextColumn::make('foo'),
        ]);
    }

    public function reorderTable(array $order, string|int|null $draggedRecordKey = null): void
    {
        parent::reorderTable($order);

        foreach (Product::whereIn('id', $order)->get() as $product) {
            $product->searchable();
        }
    }

    public function table(Table $table): Table
    {
        return parent::table($table);
    }

    protected function getDefaultTable(Table $table): Table
    {
        return $table->columns([

            SpatieMediaLibraryImageColumn::make('thumbnail')
                ->collection(config('lunar.media.collection'))
                ->conversion('small')
                ->limit(1)
                ->square()
                ->label(''),
            TextColumn::make('name')
                ->formatStateUsing(fn (Model $record): string => $record->translate('name'))
                ->label(__('lunarpanel::product.table.name.label')),
        ])->recordActions([
            DetachAction::make(),
            EditAction::make()->url(
                fn (Model $record) => ProductResource::getUrl('edit', [
                    'record' => $record,
                ])
            ),
        ])->headerActions([
            AttachAction::make()
                ->label(
                    __('lunarpanel::collection.pages.products.actions.attach.label')
                )->form([
                    ProductSelect::make('recordId')
                        ->required()
                        ->excludeAttached(),
                ])->action(function (array $arguments, array $data, Schema $schema, Table $table) {
                    $relationship = Relation::noConstraints(fn () => $table->getRelationship());

                    $product = Product::find($data['recordId']);

                    $relationship->attach($product, [
                        'position' => $relationship->count() + 1,
                    ]);
                }),
        ])->reorderable('position');
    }
}
