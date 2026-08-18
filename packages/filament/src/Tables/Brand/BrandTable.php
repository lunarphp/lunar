<?php

namespace Lunar\Filament\Tables\Brand;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Lunar\Core\Actions\Brands\DeleteBrand;
use Lunar\Core\Models\Brand;
use Lunar\Filament\Support\Concerns\CallsHooks;

class BrandTable
{
    use CallsHooks;

    public static function configure(Table $table): Table
    {
        return self::callStaticLunarHook(
            'configureTable',
            $table
                ->columns(static::getColumns())
                ->filters([])
                ->recordActions([
                    EditAction::make(),
                ])
                ->toolbarActions([
                    BulkActionGroup::make([
                        DeleteBulkAction::make()
                            ->before(function (DeleteBulkAction $action, Collection $records) {
                                if ($records->contains(fn (Brand $brand) => DeleteBrand::isProtected($brand))) {
                                    Notification::make()
                                        ->warning()
                                        ->body(__('lunarpanel::brand.action.delete.notification.error_protected'))
                                        ->send();
                                    $action->cancel();
                                }
                            }),
                    ]),
                ])
                ->searchable(),
        );
    }

    public static function getColumns(): array
    {
        return [
            SpatieMediaLibraryImageColumn::make('thumbnail')
                ->collection(config('lunar.media.collection'))
                ->conversion('small')
                ->limit(1)
                ->square()
                ->label(''),
            TextColumn::make('name')
                ->label(__('lunar-filament::brand.table.name.label'))
                ->searchable(),
            TextColumn::make('products_count')
                ->counts('products')
                ->formatStateUsing(
                    fn ($state) => number_format($state, 0)
                )
                ->label(__('lunar-filament::brand.table.products_count.label')),
        ];
    }
}
