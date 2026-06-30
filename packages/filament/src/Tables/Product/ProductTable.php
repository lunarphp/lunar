<?php

namespace Lunar\Filament\Tables\Product;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
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
                ->modifyQueryUsing(
                    fn (Builder $query): Builder => $query->with(['brand', 'productType'])
                )
                ->filters([
                    SelectFilter::make('brand')
                        ->label(__('lunar-filament::product.table.brand.label'))
                        ->relationship('brand', 'name'),
                    SelectFilter::make('status')
                        ->label(__('lunar-filament::product.table.status.label'))
                        ->options([
                            'draft' => __('lunar-filament::product.table.status.states.draft'),
                            'published' => __('lunar-filament::product.table.status.states.published'),
                            'archived' => __('lunar-filament::product.table.status.states.archived'),
                        ]),
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
                    fn (Model $record) => (string) $record->status
                )
                ->formatStateUsing(fn ($state) => __('lunar-filament::product.table.status.states.'.$state))
                ->color(fn (string $state): string => match ($state) {
                    'draft' => 'warning',
                    'published' => 'success',
                    'archived' => 'danger',
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
            TextColumn::make('variants_sum_stock_on_hand')
                ->label(__('lunar-filament::product.table.stock.label'))
                ->sum('variants', 'stock_on_hand'),
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
            TextColumn::make('public_id')
                ->label(__('lunar-filament::components.public_id.label'))
                ->toggleable(isToggledHiddenByDefault: true)
                ->copyable(),
        ];
    }

    public static function getNameColumn(): Column
    {
        return TranslatedTextColumn::make('name')
            ->fieldHydrated('name')
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
