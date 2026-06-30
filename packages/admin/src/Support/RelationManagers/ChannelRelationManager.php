<?php

namespace Lunar\Admin\Support\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ChannelRelationManager extends BaseRelationManager
{
    protected static bool $isLazy = false;

    protected static string $relationship = 'channels';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lunarpanel::relationmanagers.channels.title');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components(
            static::getFormInputs()
        );
    }

    protected static function getFormInputs(): array
    {
        return [
            Toggle::make('enabled')->label(
                __('lunarpanel::relationmanagers.channels.form.enabled.label')
            )->hint(fn (bool $state): string => match ($state) {
                false => __('lunarpanel::relationmanagers.channels.form.enabled.helper_text_false'),
                true => '',
            })->hintColor('danger')->live()->columnSpan(2),
            Grid::make(2)->schema([
                DateTimePicker::make('starts_at')->label(
                    __('lunarpanel::relationmanagers.channels.form.starts_at.label')
                )->helperText(
                    __('lunarpanel::relationmanagers.channels.form.starts_at.helper_text')
                ),
                DateTimePicker::make('ends_at')->label(
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
                AttachAction::make()->form(fn (AttachAction $action): array => [
                    $action->getRecordSelect(),
                    ...static::getFormInputs(),
                ])->recordTitle(function ($record) {
                    return $record->name;
                })->preloadRecordSelect()
                    ->label(
                        __('lunarpanel::relationmanagers.channels.actions.attach.label')
                    ),
            ])
            ->columns([
                TextColumn::make('name')->label(
                    __('lunarpanel::relationmanagers.channels.table.name.label')
                ),
                IconColumn::make('enabled')->label(
                    __('lunarpanel::relationmanagers.channels.table.enabled.label')
                )
                    ->color(fn (bool $state): string => match ($state) {
                        true => 'success',
                        false => 'warning',
                    })->icon(fn (bool $state): string => match ($state) {
                        false => 'heroicon-o-x-circle',
                        true => 'heroicon-o-check-circle',
                    }),
                TextColumn::make('starts_at')->label(
                    __('lunarpanel::relationmanagers.channels.table.starts_at.label')
                )->dateTime(),
                TextColumn::make('ends_at')->label(
                    __('lunarpanel::relationmanagers.channels.table.ends_at.label')
                )->dateTime(),
            ])->recordActions([
                EditAction::make(),
            ]);
    }
}
