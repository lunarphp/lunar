<?php

namespace Lunar\Hub\Http\Livewire\Components\Brands;

use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Hub\Tables\LunarTable;
use Lunar\LivewireTables\Components\Columns\ImageColumn;
use Lunar\LivewireTables\Components\Columns\TextColumn;
use Lunar\Models\Brand;

class BrandsTable extends LunarTable
{
    use Notifies;

    /**
     * {@inheritDoc}
     */
    public bool $searchable = true;

    /**
     * {@inheritDoc}
     */
    public bool $filterable = false;

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        $this->tableBuilder->baseColumns([
            ImageColumn::make('thumbnail', function ($record) {
                if (! $thumbnail = $record->thumbnail) {
                    return null;
                }

                return $thumbnail->getUrl('small');
            })->heading(false),
            TextColumn::make('name')->url(function ($record) {
                return route('hub.brands.show', $record->id);
            }),
            TextColumn::make('products_count'),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        return Brand::with(['thumbnail'])
            ->when($this->query, function ($query) {
                $this->resetPage();
                $query->whereIn('id', Brand::search($this->query)
                    ->query(fn ($query) => $query->select('id'))
                    ->take(1000)
                    ->keys());
            })
            ->withCount(['products'])
            ->orderBy('name')
            ->paginate($this->perPage);
    }
}
