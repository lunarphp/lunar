<?php

namespace Lunar\Admin\Filament\Resources\ChannelResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Lunar\Admin\Filament\Resources\ChannelResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;
use Lunar\Core\Contracts\Actions\Channels\DeletesChannel;
use Lunar\Core\Exceptions\ChannelActionException;
use Lunar\Core\Models\Channel;

class EditChannel extends BaseEditRecord
{
    protected static string $resource = ChannelResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->modalDescription(fn (Channel $record): string => $record->hasOrderHistory()
                    ? __('lunarpanel::channel.actions.delete.blocked')
                    : __('lunarpanel::channel.actions.delete.confirm'))
                ->disabled(fn (Channel $record): bool => $record->hasOrderHistory())
                ->tooltip(fn (Channel $record): ?string => $record->hasOrderHistory()
                    ? __('lunarpanel::channel.actions.delete.disabled_tooltip')
                    : null)
                ->using(function (Channel $record): bool {
                    try {
                        app(DeletesChannel::class)->execute($record);
                    } catch (ChannelActionException) {
                        Notification::make()
                            ->danger()
                            ->title(__('lunar-filament::channel.actions.delete.blocked'))
                            ->send();

                        return false;
                    }

                    return true;
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
