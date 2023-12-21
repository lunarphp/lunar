<?php

namespace Lunar\Admin\Support\Forms\Components;

use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;

class MailerCheckbox extends CheckboxList
{
    protected string $view = 'lunarpanel::forms.components.checkbox-list';

    public function previewAction(): Action
    {
        return Action::make('preview')->slideOver(true);
    }

    public array $mailers = [];
}

//Forms\Components\CheckboxList::make('mailers')
//    ->options(function (Forms\Get $get) {

//    })->helperText('Select which mailers you want to send.'),
//                    Forms\Components\CheckboxList::make('notifications')
//                        ->options(function (Forms\Get $get) {
//                            $mailers = config('lunar.orders.statuses.'.$get('status').'.notifications', []);
//                            return collect($mailers)->mapWithKeys(function ($mailer) {
//                                return [
//                                    class_basename($mailer) => Str::title(
//                                        Str::snake(class_basename($mailer), ' ')
//                                    ),
//                                ];
//                            });
//                        })->helperText('Select with notifications to send')