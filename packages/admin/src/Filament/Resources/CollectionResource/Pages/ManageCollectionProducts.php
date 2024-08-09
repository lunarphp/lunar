<?php

namespace Lunar\Admin\Filament\Resources\CollectionResource\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Lunar\Admin\Events\CollectionProductAttached;
use Lunar\Admin\Events\CollectionProductDetached;
use Lunar\Admin\Filament\Resources\CollectionResource;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Support\Pages\BaseManageRelatedRecords;
use Lunar\Models\Product;

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

    public function form(Form $form): Form
    {
        return $form->schema([
            Tables\Columns\TextColumn::make('foo'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([

            Tables\Columns\SpatieMediaLibraryImageColumn::make('thumbnail')
                ->collection(config('lunar.media.collection'))
                ->conversion('small')
                ->limit(1)
                ->square()
                ->label(''),
            Tables\Columns\TextColumn::make('attribute_data.name')
                ->formatStateUsing(fn (Model $record): string => $record->translateAttribute('name'))
                ->label(__('lunarpanel::product.table.name.label')),
        ])->actions([
            Tables\Actions\DetachAction::make()->after(
                fn () => CollectionProductDetached::dispatch($this->getOwnerRecord())
            ),
            Tables\Actions\EditAction::make()->url(
                fn (Model $record) => ProductResource::getUrl('edit', [
                    'record' => $record,
                ])
            ),
        ])->headerActions([
            Tables\Actions\AttachAction::make()
                ->label(
                    __('lunarpanel::collection.pages.products.actions.attach.label')
                )->form([
                    Forms\Components\Select::make('recordId')
                        ->label('Product')
                        ->required()
                        ->searchable(true)
                        ->getSearchResultsUsing(static function (Forms\Components\Select $component, string $search, ManageCollectionProducts $livewire): array {
                            $relationModel = $livewire->getRelationship()->getRelated()::class;

                            return get_search_builder($relationModel, $search)
                                ->get()
                                ->mapWithKeys(fn (Product $record): array => [$record->getKey() => $record->translateAttribute('name')])
                                ->all();
                        }),
                ])->action(function (array $arguments, array $data, Form $form, Table $table) {
                    $relationship = Relation::noConstraints(fn () => $table->getRelationship());

                    $product = Product::find($data['recordId']);

                    $relationship->attach($product, [
                        'position' => $relationship->count() + 1,
                    ]);

                    CollectionProductAttached::dispatch($this->getOwnerRecord());

                    $product->searchable();
                }),
        ])->reorderable('position');
    }
}
