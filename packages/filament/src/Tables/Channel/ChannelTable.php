<?php

namespace Lunar\Filament\Tables\Channel;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Filament\Support\Concerns\CallsHooks;

class ChannelTable
{
    use CallsHooks;

    public static function configure(Table $table): Table
    {
        return self::callStaticLunarHook(
            'configureTable',
            $table
                ->columns(static::getColumns())
                ->filters([
                    SelectFilter::make('status')
                        ->options([
                            'active' => __('lunar::states.channel.active'),
                            'inactive' => __('lunar::states.channel.inactive'),
                        ]),
                ])
                ->recordActions([
                    EditAction::make(),
                ])
                ->toolbarActions([
                    BulkActionGroup::make([
                        DeleteBulkAction::make(),
                    ]),
                ]),
        );
    }

    public static function getColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label(__('lunar-filament::channel.table.name.label')),
            TextColumn::make('default_indicator')
                ->state(fn (Model $record) => $record->default ? __('lunar-filament::channel.table.default.label') : null)
                ->badge()
                ->color('gray')
                ->label(''),
            TextColumn::make('handle')
                ->label(__('lunar-filament::channel.table.handle.label')),
            TextColumn::make('url')
                ->label(__('lunar-filament::channel.table.url.label')),
        ];
    }
}
