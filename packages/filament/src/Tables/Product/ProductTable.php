<?php

namespace Lunar\Filament\Tables\Product;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Filament\Support\Concerns\CallsHooks;
use Lunar\Filament\Tables\Columns\TranslatedTextColumn;

class ProductTable
{
    use CallsHooks;

    public static function configure(Table $table): Table
    {
        return self::callStaticLunarHook(
            'configureTable',
            $table
                ->columns(static::getColumns())
                ->filters([
                    SelectFilter::make('brand')
                        ->label(__('lunar-filament::product.table.brand.label'))
                        ->relationship('brand', 'name'),
                    TrashedFilter::make(),
                ])
                ->recordActions([
                    EditAction::make(),
                ])
                ->toolbarActions([
                    BulkActionGroup::make([
                        DeleteBulkAction::make(),
                    ]),
                ])
                ->selectCurrentPageOnly()
                ->deferLoading(),
        );
    }

    public static function getColumns(): array
    {
        return [
            TextColumn::make('status')
                ->label(__('lunar-filament::product.table.status.label'))
                ->badge()
                ->getStateUsing(
                    fn (Model $record) => $record->deleted_at ? 'deleted' : $record->status
                )
                ->formatStateUsing(fn ($state) => __('lunar-filament::product.table.status.states.'.$state))
                ->color(fn (string $state): string => match ($state) {
                    'draft' => 'warning',
                    'published' => 'success',
                    'deleted' => 'danger',
                    default => 'primary',
                }),
            SpatieMediaLibraryImageColumn::make('thumbnail')
                ->collection(config('lunar.media.collection'))
                ->conversion('small')
                ->filterMediaUsing(fn ($media) => $media->where('custom_properties.primary', true)->count() ? $media->where('custom_properties.primary', true) : $media)
                ->limit(1)
                ->square()
                ->label(''),
            static::getNameColumn(),
            TextColumn::make('brand.name')
                ->label(__('lunar-filament::product.table.brand.label'))
                ->toggleable()
                ->searchable(),
            static::getSkuColumn(),
            TextColumn::make('variants_sum_stock')
                ->label(__('lunar-filament::product.table.stock.label'))
                ->sum('variants', 'stock'),
            TextColumn::make('productType.name')
                ->label(__('lunar-filament::product.table.producttype.label'))
                ->limit(30)
                ->tooltip(function (TextColumn $column): ?string {
                    $state = $column->getState();

                    if (strlen($state) <= $column->getCharacterLimit()) {
                        return null;
                    }

                    return $state;
                })
                ->toggleable(),
        ];
    }

    public static function getNameColumn(): Column
    {
        return TranslatedTextColumn::make('attribute_data.name')
            ->attributeData()
            ->limitedTooltip()
            ->limit(50)
            ->label(__('lunar-filament::product.table.name.label'))
            ->searchable();
    }

    public static function getSkuColumn(): Column
    {
        return TextColumn::make('variants.sku')
            ->label(__('lunar-filament::product.table.sku.label'))
            ->tooltip(function (TextColumn $column, Model $record): ?string {

                if ($record->variants->count() <= $column->getListLimit()) {
                    return null;
                }

                if ($record->variants->count() > 30) {
                    $record->variants = $record->variants->slice(0, 30);
                }

                return $record->variants
                    ->map(fn ($variant) => $variant->sku)
                    ->implode(', ');
            })
            ->listWithLineBreaks()
            ->limitList(1)
            ->toggleable()
            ->searchable();
    }
}
