<?php

namespace Lunar\Shipping\Filament\Resources\ShippingMethodResource\Widgets;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;

class AvailabilityScheduleWidget extends Widget implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected string $view = 'shipping::widgets.availability-schedule';

    public ?Model $record = null;

    public array $data = [];

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    public function mount(): void
    {
        $schedule = $this->record?->data['schedule'] ?? [];

        $this->form->fill([
            'schedule' => $schedule,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        $days = [
            1 => __('lunarpanel.shipping::shippingmethod.form.schedule.days.monday'),
            2 => __('lunarpanel.shipping::shippingmethod.form.schedule.days.tuesday'),
            3 => __('lunarpanel.shipping::shippingmethod.form.schedule.days.wednesday'),
            4 => __('lunarpanel.shipping::shippingmethod.form.schedule.days.thursday'),
            5 => __('lunarpanel.shipping::shippingmethod.form.schedule.days.friday'),
            6 => __('lunarpanel.shipping::shippingmethod.form.schedule.days.saturday'),
            7 => __('lunarpanel.shipping::shippingmethod.form.schedule.days.sunday'),
        ];

        $rows = collect($days)->map(fn ($label, $day) => Group::make([
            Forms\Components\Checkbox::make('enabled')
                ->label($label)
                ->live()
                ->columnSpan(1),
            Forms\Components\TimePicker::make('from')
                ->label(__('lunarpanel.shipping::shippingmethod.form.schedule.from.label'))
                ->seconds(false)
                ->disabled(fn (Get $get) => ! $get('enabled'))
                ->columnSpan(1),
            Forms\Components\TimePicker::make('to')
                ->label(__('lunarpanel.shipping::shippingmethod.form.schedule.to.label'))
                ->seconds(false)
                ->disabled(fn (Get $get) => ! $get('enabled'))
                ->rules(fn (Get $get): array => filled($get('from')) ? ['after:'.$get('from')] : [])
                ->validationMessages([
                    'after' => __('lunarpanel.shipping::shippingmethod.form.schedule.to.validation.after'),
                ])
                ->columnSpan(1),
        ])->statePath((string) $day)->columns(3)
        )->values()->toArray();

        return $schema
            ->components([
                Section::make(__('lunarpanel.shipping::shippingmethod.form.schedule.label'))
                    ->schema($rows)
                    ->statePath('schedule'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        $data = $this->record->data ? $this->record->data->toArray() : [];
        $data['schedule'] = $state['schedule'];

        $this->record->data = $data;
        $this->record->save();

        Notification::make()
            ->title(__('filament-panels::resources/pages/edit-record.notifications.saved.title'))
            ->success()
            ->send();
    }

    public function saveAction(): Action
    {
        return Action::make('save')
            ->label(__('filament-panels::resources/pages/edit-record.form.actions.save.label'))
            ->submit('save');
    }
}
