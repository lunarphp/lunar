<?php

namespace Lunar\Admin\Filament\Resources\ProductResource\Widgets;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;
use Lunar\Models\ProductOption;

class ProductOptionsWidget extends BaseWidget implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string $view = 'lunarpanel::resources.product-resource.widgets.product-options';

    public ?Model $record;

    public array $variants = [];

    public bool $configuringOptions = false;

    public function mount()
    {
        //        dd($this->record);
    }

    public function cancelOptionConfiguring(): void
    {
        $this->configuringOptions = false;
    }

    public function query()
    {
        return $this->record->productOptions()
            ->with('values', function ($query) {
                $query->whereHas('variants', function ($relation) {
                    $relation->whereIn($relation->getModel()->getTable().'.id', $this->record->variants()->pluck('id'));
                });
            });
    }

    public function table(Table $table)
    {
        return $table->query(
            fn () => $this->query()
        )->heading('Product Options')
            ->headerActions([
                Action::make('configure')->label('Configure Options')
                    ->action(
                        fn () => $this->configuringOptions = true
                    ),
            ])
            ->columns([
                TextColumn::make('name')
                    ->formatStateUsing(
                        fn (ProductOption $productOption) => $productOption->translate('name')
                    ),
                TextColumn::make('values')->formatStateUsing(
                    fn (ProductOption $productOption) => $productOption->values->map(
                        fn ($value) => $value->translate('name')
                    )->join(', ')
                ),
            ])->paginated(false);
    }
}
