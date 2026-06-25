<?php

namespace Lunar\Admin\Filament\Resources\OrderResource\Pages;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Lunar\Admin\Filament\Resources\OrderResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;

class EditOrder extends BaseEditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            Action::make('payment related actions')
                ->color('gray')
                ->url('#'),
            Action::make('download_pdf')
                ->label(__('lunarpanel::order.action.download_order_pdf.label'))
                ->action(function () {
                    Notification::make()->title(__('lunarpanel::order.action.download_order_pdf.notification'))->success()->send();

                    return response()->streamDownload(function () {
                        echo Pdf::loadView('lunarpanel::pdf.order', [
                            'record' => $this->record,
                        ])->stream();
                    }, name: "Order-{$this->record->reference}.pdf");
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
