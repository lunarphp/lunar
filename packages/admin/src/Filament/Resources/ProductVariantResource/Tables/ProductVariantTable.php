<?php

namespace Lunar\Admin\Filament\Resources\ProductVariantResource\Tables;

use Filament\Tables\Table;
use Lunar\Admin\Support\Concerns\CallsHooks;

class ProductVariantTable
{
    use CallsHooks;

    public static function configure(Table $table): Table
    {
        return self::callStaticLunarHook(
            'configureTable',
            $table
                ->columns(static::getColumns())
                ->filters([])
                ->recordActions([])
                ->toolbarActions([])
                ->selectCurrentPageOnly()
                ->deferLoading(),
        );
    }

    public static function getColumns(): array
    {
        return [];
    }
}
