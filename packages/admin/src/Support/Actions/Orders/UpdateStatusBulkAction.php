<?php

namespace Lunar\Admin\Support\Actions\Orders;

use Filament\Forms;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Lunar\Admin\Support\Actions\Traits\UpdatesOrderStatus;
use Lunar\Facades\DB;

class UpdateStatusBulkAction extends BulkAction
{
    use UpdatesOrderStatus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(
            __('lunarpanel::actions.orders.update_status.label')
        );

        $this->modalWidth(MaxWidth::TwoExtraLarge);

        $this->form([
            Forms\Components\Select::make('status')
                ->label(__('lunarpanel::order.form.status.label'))
                ->default(fn ($record) => $record?->status)
                ->options(fn () => collect(config('lunar.orders.statuses', []))
                    ->mapWithKeys(fn ($data, $status) => [$status => $data['label']]))
                ->required()
                ->live(),
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
            Forms\Components\Textarea::make('additional_content')->helperText(
                'This content will be added to the email, if supported'
            ),
        ]);

        $this->action(
            function (Collection $records, array $data) {
                DB::beginTransaction();
                foreach ($records as $record) {
                    $this->updateStatus($record, $data);
                }
                DB::commit();
            }
        );
    }
}
