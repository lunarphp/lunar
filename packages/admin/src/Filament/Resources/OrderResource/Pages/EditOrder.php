<?php

namespace Lunar\Admin\Filament\Resources\OrderResource\Pages;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Lunar\Admin\Filament\Resources\OrderResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;

class EditOrder extends BaseEditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            Actions\Action::make('payment related actions')
                ->color('gray')
                ->url('#'),
            Actions\Action::make('update_status')
                ->label(__('lunarpanel::order.action.update_status.label'))
                ->form([
                    Forms\Components\Select::make('status')
                        ->label(__('lunarpanel::order.form.status.label'))
                        ->default($this->record->status)
                        ->options(fn () => collect(config('lunar.orders.statuses', []))
                            ->mapWithKeys(fn ($data, $status) => [$status => $data['label']]))
                        ->required(),
                    Forms\Components\Placeholder::make('additional content and mailer'),
                ])
                ->modalWidth('md')
                ->slideOver()
                ->action(fn ($record, $data) => $record
                    ->update([
                        'status' => $data['status'],
                    ]))
                ->after(fn () => Notification::make()->title(__('lunarpanel::order.action.update_status.notification'))->success()->send()),
            Actions\Action::make('download_pdf')
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
