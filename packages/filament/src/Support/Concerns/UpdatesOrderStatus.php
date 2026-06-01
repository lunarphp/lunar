<?php

namespace Lunar\Filament\Support\Concerns;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Lunar\Core\Contracts\Actions\Orders\UpdatesOrderStatus as UpdatesOrderStatusContract;
use Lunar\Core\Contracts\OrderStateConfig;
use Lunar\Core\Models\Order;

trait UpdatesOrderStatus
{
    protected static function getAdditionalContentInput(): Textarea
    {
        return Textarea::make('additional_content')
            ->label(__('lunar-filament::order.action.update_status.additional_content.label'))
            ->hidden(function (Get $get) {
                return ! count(
                    static::getMailers($get('status'))
                );
            });
    }

    protected static function getStatusSelectInput(): Select
    {
        return Select::make('status')
            ->label(__('lunar-filament::order.action.update_status.new_status.label'))
            ->default(fn ($record) => $record ? (string) $record->status : null)
            ->options(fn ($record) => collect(app(OrderStateConfig::class)->orderStates())
                ->mapWithKeys(fn (string $class) => [$class::$name => (new $class($record ?? new Order))->label()]))
            ->required()
            ->live();
    }

    protected static function getEmailAddressesInput(): CheckboxList
    {
        return CheckboxList::make('email_addresses')
            ->hidden(function (Get $get, ?Order $record = null) {

                if (! $record) {
                    return true;
                }

                return ! count($get('mailers') ?: [])
                    || ! ($record?->billingAddress?->contact_email || $record->shippingAddress?->contact_email);
            })->afterStateHydrated(function (?Order $record, CheckboxList $component) {
                $emails = collect([
                    $record?->billingAddress?->contact_email,
                    $record?->shippingAddress?->contact_email,
                ])->filter()->unique()->map(
                    fn ($email) => $email
                )->values()->toArray();

                $component->state($emails);
            })->options(function (?Order $record = null) {
                return collect([
                    $record?->billingAddress?->contact_email,
                    $record?->shippingAddress?->contact_email,
                ])->filter()->unique()->mapWithKeys(
                    fn ($email) => [$email => $email]
                )->toArray();
            });
    }

    protected static function getAdditionalEmailInput(): TextInput
    {
        return TextInput::make('additional_email')
            ->label(__('lunar-filament::order.action.update_status.additional_email_recipient.label'))
            ->placeholder(__('lunar-filament::order.action.update_status.additional_email_recipient.placeholder'))
            ->hidden(function (Get $get) {
                return ! count(
                    static::getMailers($get('status'))
                );
            });
    }

    protected static function getMailersCheckboxInput(): CheckboxList
    {
        return CheckboxList::make('mailers')->options(function (Get $get) {
            $mailers = config('lunar.orders.statuses.'.$get('status').'.mailers', []);

            return collect($mailers)->mapWithKeys(function ($mailer) {
                return [
                    $mailer => Str::title(
                        Str::snake(class_basename($mailer), ' ')
                    ),
                ];
            });
        })->hidden(function (Get $get) {
            return ! count(
                static::getMailers($get('status'))
            );
        })->live();
    }

    protected function getFormSteps()
    {
        return [
            static::getStatusSelectInput(),
            Group::make([
                static::getMailersCheckboxInput(),
                Group::make([
                    static::getAdditionalContentInput(),
                    static::getEmailAddressesInput(),
                    static::getAdditionalEmailInput(),
                ])->hidden(function (Get $get) {
                    return ! count($get('mailers')) ||
                        ! count(
                            static::getMailers($get('status'))
                        );
                }),
            ])->hidden(function (Get $get) {
                return ! count(
                    static::getMailers($get('status'))
                );
            }),
        ];
    }

    protected function updateStatus(Order $record, array $data)
    {
        app(UpdatesOrderStatusContract::class)->execute($record, $data['status']);

        if (isset($data['send_notifications']) && ! $data['send_notifications']) {
            Notification::make()->title(
                __('lunar-filament::actions.orders.update_status.notification.label')
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
            __('lunar-filament::actions.orders.update_status.notification.label')
        )->success()->send();
    }

    protected static function getMailers(?string $status = null): array
    {
        if (! $status) {
            return [];
        }

        return config("lunar.orders.statuses.{$status}.mailers", []);
    }
}
