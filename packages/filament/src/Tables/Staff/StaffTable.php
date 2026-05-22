<?php

namespace Lunar\Filament\Tables\Staff;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Filament\Support\Concerns\CallsHooks;

class StaffTable
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
                        DeleteBulkAction::make(),
                    ]),
                ]),
        );
    }

    public static function getColumns(): array
    {
        return [
            TextColumn::make('first_name')
                ->label(__('lunar-filament::staff.table.first_name.label')),
            TextColumn::make('last_name')
                ->label(__('lunar-filament::staff.table.last_name.label')),
            TextColumn::make('email')
                ->label(__('lunar-filament::staff.table.email.label')),
            TextColumn::make('admin')
                ->label('')
                ->badge()
                ->state(function (Model $record): string {
                    return $record->admin ? __('lunar-filament::staff.table.admin.badge') : '';
                }),
        ];
    }
}
