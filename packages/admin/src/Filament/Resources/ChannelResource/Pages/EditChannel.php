<?php

namespace Lunar\Admin\Filament\Resources\ChannelResource\Pages;

use Filament\Actions\DeleteAction;
use Lunar\Admin\Filament\Resources\ChannelResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;
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
                    : null),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
