<?php

namespace Lunar\Admin\Support\RelationManagers;

use Filament;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Lunar\Admin\Events\ModelChannelsUpdated;

class ChannelRelationManager extends BaseRelationManager
{
    protected static bool $isLazy = false;

    protected static string $relationship = 'channels';

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
            Filament\Forms\Components\Toggle::make('enabled')->label(
                __('lunarpanel::relationmanagers.channels.form.enabled.label')
            )->hint(fn (bool $state): string => match ($state) {
                false => __('lunarpanel::relationmanagers.channels.form.enabled.helper_text_false'),
                true => '',
            })->hintColor('danger')->live()->columnSpan(2),
            Filament\Forms\Components\Grid::make(2)->schema([
                Filament\Forms\Components\DateTimePicker::make('starts_at')->label(
                    __('lunarpanel::relationmanagers.channels.form.starts_at.label')
                )->helperText(
                    __('lunarpanel::relationmanagers.channels.form.starts_at.helper_text')
                ),
                Filament\Forms\Components\DateTimePicker::make('ends_at')->label(
                    __('lunarpanel::relationmanagers.channels.form.ends_at.label')
                )->helperText(
                    __('lunarpanel::relationmanagers.channels.form.ends_at.helper_text')
                ),
            ]),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->description(
                __('lunarpanel::relationmanagers.channels.table.description')
            )->paginated(false)
            ->headerActions([
                Tables\Actions\AttachAction::make()->form(fn (Tables\Actions\AttachAction $action): array => [
                    $action->getRecordSelect(),
                    ...static::getFormInputs(),
                ])->recordTitle(function ($record) {
                    return $record->name;
                })->after(
                    fn () => sync_with_search(
                        $this->getOwnerRecord()
                    )
                )->preloadRecordSelect()
                    ->label(
                        __('lunarpanel::relationmanagers.channels.actions.attach.label')
                    ),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('name')->label(
                    __('lunarpanel::relationmanagers.channels.table.name.label')
                ),
                Tables\Columns\IconColumn::make('enabled')->label(
                    __('lunarpanel::relationmanagers.channels.table.enabled.label')
                )
                    ->color(fn (bool $state): string => match ($state) {
                        true => 'success',
                        false => 'warning',
                    })->icon(fn (bool $state): string => match ($state) {
                        false => 'heroicon-o-x-circle',
                        true => 'heroicon-o-check-circle',
                    }),
                Tables\Columns\TextColumn::make('starts_at')->label(
                    __('lunarpanel::relationmanagers.channels.table.starts_at.label')
                )->dateTime(),
                Tables\Columns\TextColumn::make('ends_at')->label(
                    __('lunarpanel::relationmanagers.channels.table.ends_at.label')
                )->dateTime(),
            ])->actions([
                Tables\Actions\EditAction::make()->after(
                    fn () => ModelChannelsUpdated::dispatch(
                        $this->getOwnerRecord()
                    )
                ),
            ]);
    }
}
