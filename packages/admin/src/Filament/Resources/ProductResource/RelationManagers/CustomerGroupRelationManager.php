<?php

namespace Lunar\Admin\Filament\Resources\ProductResource\RelationManagers;

use Filament;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CustomerGroupRelationManager extends RelationManager
{
    protected static string $relationship = 'customerGroups';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return $form->schema(
            static::getFormInputs()
        );
    }

    protected static function getFormInputs(): array
    {
        return [
            Filament\Forms\Components\Grid::make(3)->schema([
                Filament\Forms\Components\Toggle::make('enabled')->label(
                    __('lunarpanel::relationmanagers.customer_groups.form.enabled.label')
                ),
                Filament\Forms\Components\Toggle::make('visible')->label(
                    __('lunarpanel::relationmanagers.customer_groups.form.visible.label')
                ),
                Filament\Forms\Components\Toggle::make('purchasable')->label(
                    __('lunarpanel::relationmanagers.customer_groups.form.purchasable.label')
                ),
            ]),

            Filament\Forms\Components\Grid::make(2)->schema([
                Filament\Forms\Components\DateTimePicker::make('starts_at')->label(
                    __('lunarpanel::relationmanagers.customer_groups.form.starts_at.label')
                ),
                Filament\Forms\Components\DateTimePicker::make('ends_at')->label(
                    __('lunarpanel::relationmanagers.customer_groups.form.ends_at.label')
                ),
            ]),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->description(
                __('lunarpanel::relationmanagers.customer_groups.table.description')
            )
            ->paginated(false)
            ->headerActions([
                Tables\Actions\AttachAction::make()->form(fn (Tables\Actions\AttachAction $action): array => [
                    $action->getRecordSelect(),
                    ...static::getFormInputs(),
                ])->recordTitle(function ($record) {
                    return $record->name;
                })->preloadRecordSelect()
                    ->label(
                        __('lunarpanel::relationmanagers.customer_groups.actions.attach.label')
                    ),
            ])->columns([
                Tables\Columns\TextColumn::make('name')->label(
                    __('lunarpanel::relationmanagers.customer_groups.table.name.label')
                ),
                Tables\Columns\IconColumn::make('enabled')->label(
                    __('lunarpanel::relationmanagers.customer_groups.table.enabled.label')
                )
                    ->color(fn (string $state): string => match ($state) {
                        '1' => 'success',
                        '0' => 'warning',
                    })->icon(fn (string $state): string => match ($state) {
                        '0' => 'heroicon-o-x-circle',
                        '1' => 'heroicon-o-check-circle',
                    }),
                Tables\Columns\IconColumn::make('visible')->label(
                    __('lunarpanel::relationmanagers.customer_groups.table.visible.label')
                )
                    ->color(fn (string $state): string => match ($state) {
                        '1' => 'success',
                        '0' => 'warning',
                    })->icon(fn (string $state): string => match ($state) {
                        '0' => 'heroicon-o-x-circle',
                        '1' => 'heroicon-o-check-circle',
                    }),
                Tables\Columns\IconColumn::make('purchasable')->label(
                    __('lunarpanel::relationmanagers.customer_groups.table.purchasable.label')
                )
                    ->color(fn (string $state): string => match ($state) {
                        '1' => 'success',
                        '0' => 'warning',
                    })->icon(fn (string $state): string => match ($state) {
                        '0' => 'heroicon-o-x-circle',
                        '1' => 'heroicon-o-check-circle',
                    }),
                Tables\Columns\TextColumn::make('starts_at')->label(
                    __('lunarpanel::relationmanagers.customer_groups.table.starts_at.label')
                )->dateTime(),
                Tables\Columns\TextColumn::make('ends_at')->label(
                    __('lunarpanel::relationmanagers.customer_groups.table.ends_at.label')
                )->dateTime(),
            ])->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }
}
