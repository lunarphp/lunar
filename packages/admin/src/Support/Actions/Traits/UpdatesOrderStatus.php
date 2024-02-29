<?php

namespace Lunar\Admin\Support\Actions\Traits;

use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Lunar\Models\Order;

trait UpdatesOrderStatus
{
    protected static function getAdditionalContentInput(): Forms\Components\Textarea
    {
        return Forms\Components\Textarea::make('additional_content')
            ->label(__('lunarpanel::order.action.update_status.additional_content.label'))
            ->hidden(function (Forms\Get $get) {
                return ! count(
                    static::getMailers($get('status'))
                );
            })->hidden(function (Forms\Get $get) {
                return ! count(
                    static::getMailers($get('status'))
                );
            });
    }

    protected static function getStatusSelectInput(): Forms\Components\Select
    {
        return Forms\Components\Select::make('status')
            ->label(__('lunarpanel::order.action.update_status.new_status.label'))
            ->default(fn ($record) => $record?->status)
            ->options(fn () => collect(config('lunar.orders.statuses', []))
                ->mapWithKeys(fn ($data, $status) => [$status => $data['label']]))
            ->required()
            ->live();
    }

    protected static function getEmailAddressesInput(): Forms\Components\CheckboxList
    {
        return Forms\Components\CheckboxList::make('email_addresses')
            ->hidden(function (Forms\Get $get, Order $record = null) {
                if (! $record) {
                    return true;
                }

                return ! count($get('mailers') ?: [])
                    || ! ($record?->billingAddress?->contact_email && $record->shippingAddress->contact_email);
            })->options(function (Order $record = null) {
                return collect([
                    $record?->billingAddress->contact_email,
                    $record?->shippingAddress->contact_email,
                ])->filter()->unique()->mapWithKeys(
                    fn ($email) => [$email => $email]
                )->toArray();
            });
    }

    protected static function getAdditionalEmailInput(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('additional_email')
            ->label(__('lunarpanel::order.action.update_status.additional_email_recipient.label'))
            ->placeholder(__('lunarpanel::order.action.update_status.additional_email_recipient.placeholder'))
            ->hidden(function (Forms\Get $get) {
                return ! count(
                    static::getMailers($get('status'))
                );
            });
    }

    protected static function getMailersCheckboxInput(): Forms\Components\CheckboxList
    {
        return Forms\Components\CheckboxList::make('mailers')->options(function (Forms\Get $get) {
            $mailers = config('lunar.orders.statuses.'.$get('status').'.mailers', []);

            return collect($mailers)->mapWithKeys(function ($mailer) {
                return [
                    $mailer => Str::title(
                        Str::snake(class_basename($mailer), ' ')
                    ),
                ];
            });
        })->hidden(function (Forms\Get $get) {
            return ! count(
                static::getMailers($get('status'))
            );
        });
    }

    protected function getFormSteps()
    {
        return [
            static::getStatusSelectInput(),
            Forms\Components\Group::make([
                static::getMailersCheckboxInput(),
                static::getAdditionalContentInput(),
                static::getEmailAddressesInput(),
                static::getAdditionalEmailInput(),
            ])->hidden(function (Forms\Get $get) {
                return ! count(
                    static::getMailers($get('status'))
                );
            })
        ];
    }

    protected function updateStatus(Order $record, array $data)
    {
        $record->update([
            'status' => $data['status'],
        ]);

        if (isset($data['send_notifications']) && ! $data['send_notifications']) {
            Notification::make()->title(
                __('lunarpanel::actions.orders.update_status.notification.label')
            )->success()->send();

            return;
        }

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

    protected static function getMailers(string $status = null): array
    {
        if (! $status) {
            return [];
        }

        return config("lunar.orders.statuses.{$status}.mailers", []);
    }
}
