<?php

namespace Lunar\Admin\Support\Actions\Traits;

use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Lunar\Admin\Support\Forms\Components\MailerSelect;
use Lunar\Models\Order;

trait UpdatesOrderStatus
{
    protected function getFormSteps()
    {
        return [
            Forms\Components\Wizard\Step::make('Status')->schema([
                Forms\Components\Select::make('status')
                    ->label(__('lunarpanel::order.form.status.label'))
                    ->default(fn ($record) => $record?->status)
                    ->options(fn () => collect(config('lunar.orders.statuses', []))
                        ->mapWithKeys(fn ($data, $status) => [$status => $data['label']]))
                    ->required()
                    ->live(),
            ]),
            Forms\Components\Wizard\Step::make('Mailers & Notifications')->schema([
                Forms\Components\CheckboxList::make('mailers')->options(function (Forms\Get $get) {
                    $mailers = config('lunar.orders.statuses.'.$get('status').'.mailers', []);

                    return collect($mailers)->mapWithKeys(function ($mailer) {
                        return [
                            $mailer => Str::title(
                                Str::snake(class_basename($mailer), ' ')
                            ),
                        ];
                    });
                }),
                Forms\Components\Textarea::make('additional_content')->hidden(function (Forms\Get $get) {
                    return ! count(
                        $this->getMailers($get('status'))
                    );
                }),
                Forms\Components\CheckboxList::make('email_addresses')
                    ->hidden(function (Forms\Get $get, Collection $records, Order $record = null) {
                        if (! $record) {
                            return true;
                        }

                        return ! count($get('mailers') ?: [])
                            || ! ($record?->billingAddress?->contact_email && $record->shippingAddress->contact_email);
                    })->options(function (Order $record) {
                        return collect([
                            $record?->billingAddress->contact_email,
                            $record?->shippingAddress->contact_email,
                        ])->filter()->unique()->mapWithKeys(
                            fn ($email) => [$email => $email]
                        )->toArray();
                    }),
                Forms\Components\TextInput::make('additional_email')->hidden(function (Forms\Get $get) {
                    return ! count(
                        $this->getMailers($get('status'))
                    );
                }),
            ])->hidden(function (Forms\Get $get) {
                return ! count(
                    $this->getMailers($get('status'))
                );
            }),

            Forms\Components\Wizard\Step::make('Preview & Send')->schema(function (Forms\Get $get, Model $record) {
                $mailers = $get('mailers');

                return [
                    MailerSelect::make('mailer')->context($record)->mailers($mailers)->additionalContent(
                        $get('additionalContent')
                    ),
                ];
            }),
        ];
    }

    protected function updateStatus(Order $record, array $data)
    {
        $record->update([
            'status' => $data['status'],
        ]);

        $emails = collect(
            [...$data['email_addresses'] ?? [], $data['additional_email'] ?? null]
        )->filter()->unique();

        foreach ($data['mailers'] ?? [] as $mailerClass) {
            $mailable = new $mailerClass($record, $data['additional_content']);
            $mailable->with('order', $record)
                ->with('content', $data['additional_content']);
            foreach ($emails as $email) {
                Mail::to($email)
                    ->queue($mailable);

                $storedPath = 'orders/activity/'.Str::random().'.html';

                Storage::put(
                    $storedPath,
                    $mailable->render()
                );

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($record)
                    ->event('email-notification')
                    ->withProperties([
                        'template' => $storedPath,
                        'email' => $email,
                        'mailer' => $mailerClass,
                    ])->log('email-notification');
            }
        }

        Notification::make()->title(
            __('lunarpanel::actions.orders.update_status.notification.label')
        )->success()->send();
    }

    protected function getMailers(string $status = null): array
    {
        if (! $status) {
            return [];
        }

        return config("lunar.orders.statuses.{$status}.mailers", []);
    }
}
