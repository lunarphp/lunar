<?php

namespace Lunar\Filament\Tables\Language;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Support\Concerns\CallsHooks;

class LanguageTable
{
    use CallsHooks;

    public static function configure(Table $table): Table
    {
        return self::callStaticLunarHook(
            'configureTable',
            $table->columns(static::getColumns()),
        );
    }

    public static function getColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label(__('lunarpanel::language.table.name.label')),
            TextColumn::make('default_indicator')
                ->state(fn (Model $record) => $record->default ? __('lunarpanel::language.table.default.label') : null)
                ->badge()
                ->color('gray')
                ->label(''),
            TextColumn::make('code')
                ->label(__('lunarpanel::language.table.code.label')),
        ];
    }
}
