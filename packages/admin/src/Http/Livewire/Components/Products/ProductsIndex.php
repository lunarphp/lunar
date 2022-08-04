<?php

namespace GetCandy\Hub\Http\Livewire\Components\Products;

use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Hub\Http\Livewire\Traits\SearchesProducts;
use GetCandy\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Contracts\Database\Query\Builder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use GetCandy\Hub\Tables\Columns\AttributeColumn;
use GetCandy\Hub\Tables\Columns\StatusColumn;
use GetCandy\Hub\Tables\Columns\ThumbnailColumn;

class ProductsIndex extends Component implements HasTable
{
    use InteractsWithTable;

    public function isTableSearchable(): bool
    {
        return true;
    }

    protected function getTableQuery(): Builder
    {
        return Product::query();
    }

    protected function applySearchToTableQuery(Builder $query): Builder
    {
        if (filled($searchQuery = $this->getTableSearchQuery())) {
            $query->whereIn('id', Product::search($searchQuery)->keys());
        }

        return $query;
    }

    public function getTableColumns(): array
    {
        return [
            StatusColumn::make('status'),
            ThumbnailColumn::make('thumbnail')->label(''),
            AttributeColumn::make('name'),
        ];
    }

//     use WithPagination;
//     use SearchesProducts;
//     use Notifies;
//
//     public $selectPage = false;
//     public $selectAll = false;
//     public $selected = [];
//
//     /**
//      * The search term.
//      *
//      * @var string
//      */
//     public $search = '';
//
//     /**
//      * The search filters.
//      *
//      * @var array
//      */
//     public $filters = [
//         'status' => null,
//         'soft_deleted' => false,
//     ];
//
//     /**
//      * Define what to track in the query string.
//      *
//      * @var array
//      */
//     protected $queryString = ['search', 'filters'];
//
//     public function updatedSelectPage($value)
//     {
//         $this->selected = $value
//             ? $this->products->pluck('id')->map(fn ($id) => (string) $id)
//             : [];
//     }
//
//     public function selectAll()
//     {
//         $this->selectAll = true;
//     }
//
//     public function getProductsProperty()
//     {
//         $query = Product::search($this->search);
//
//         if ($this->filters['soft_deleted']) {
//             $query->onlyTrashed();
//         }
//
//         if ($status = $this->filters['status'] ?? null) {
//             $query->where('status', $status);
//         }
//
//         return tap($query->paginate(50), function ($products) {
//             return $products->load(['thumbnail', 'productType', 'variants']);
//         });
//     }
//
//     /**
//      * Get the listing thumbnail for a product.
//      *
//      * @param  Product  $product
//      * @return void
//      */
//     public function getThumbnail($product)
//     {
//         if ($product->thumbnail) {
//             return $product->thumbnail;
//         }
//
//         $variant = $product->variants->first(function ($variant) {
//             return $variant->thumbnail;
//         });
//
//         return $variant?->thumbnail;
//     }
//
//     public function restoreProduct($productId)
//     {
//         Product::onlyTrashed()->find($productId)->restore();
//
//         $this->notify(
//             __('adminhub::notifications.products.product_restored')
//         );
//     }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.products.index')
            ->layout('adminhub::layouts.base');
    }
}
