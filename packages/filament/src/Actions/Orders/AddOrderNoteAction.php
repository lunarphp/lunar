<?php

namespace Lunar\Filament\Actions\Orders;

use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Lunar\Core\Models\Order;

/**
 * Filament-only single-field write to set the order's `notes` column.
 * Graduates to a core action if note-writes need to fire events or hit the
 * activity log.
 */
class AddOrderNoteAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'add_note';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('lunar-filament::actions.orders.add_note.label'))
            ->modalHeading(__('lunar-filament::actions.orders.add_note.modal_heading'))
            ->icon('heroicon-o-pencil-square')
            ->fillForm(fn (Order $record): array => [
                'notes' => $record->notes,
            ])
            ->schema([
                Textarea::make('notes')
                    ->label(__('lunar-filament::actions.orders.add_note.fields.note.label'))
                    ->maxLength(65_535)
                    ->required(),
            ])
            ->action(function (Order $record, array $data): void {
                $record->forceFill(['notes' => $data['notes']])->save();
            })
            ->successNotificationTitle(__('lunar-filament::actions.orders.add_note.notification.success'));
    }
}
