<?php

namespace Lunar\Admin\Livewire\Components;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Support\Enums\ActionSize;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;
use Lunar\Admin\Support\Facades\ActivityLog;
use Lunar\Facades\ModelManifest;

class ActivityLogFeed extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;
    use WithPagination;

    const UPDATED = 'activityUpdated';

    protected $listeners = [
        self::UPDATED => '$refresh',
    ];

    /**
     * The log subject to get activity for.
     */
    #[Locked]
    public Model $subject;

    public ?string $comment = null;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Textarea::make('comment')
                    ->placeholder(__('lunarpanel::components.activity-log.input.placeholder'))
                    ->required()
                    ->extraInputAttributes(['style' => 'min-height: 50px'])
                    ->hiddenLabel(),
            ]);
    }

    public function addCommentAction(): Action
    {
        return Action::make('addComment')
            ->label(__('lunarpanel::components.activity-log.action.add-comment'))
            ->action(fn () => $this->addComment())
            ->size(ActionSize::ExtraSmall)
            ->after(function () {
                Notification::make()
                    ->title(__('lunarpanel::components.activity-log.notification.comment_added'))
                    ->success()
                    ->send();

                $this->comment = null;

                $this->resetPage($this->pageName);
            });
    }

    /**
     * Add a comment to the order.
     *
     * @return void
     */
    public function addComment()
    {
        $data = $this->form->getState();

        activity()
            ->useLog('lunarpanel')
            ->performedOn($this->subject)
            ->causedBy(Filament::auth()->user())
            ->event('comment')
            ->withProperties(['content' => $data['comment']])
            ->log('comment');
    }

    #[Computed()]
    public function pageName(): string
    {
        return str(class_basename($this->subject))->append('Timeline')->camel();
    }

    /**
     * Returns the activity log for the subject.
     */
    #[Computed]
    public function activityLog(): \Illuminate\Pagination\LengthAwarePaginator
    {
        $activities = $this->subject->activities()
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], $this->pageName);

        $activities->setCollection($activities->groupBy(function ($log) {
            return $log->created_at->format('Y-m-d');
        })
            ->map(function ($logs) {
                return [
                    'date' => $logs->first()->created_at->startOfDay(),
                    'items' => $logs->map(function ($log) {
                        return [
                            'log' => $log,
                            'renderers' => $this->renderers->filter(function ($render) use ($log) {
                                return $render['event'] == $log->event;
                            })->pluck('class'),
                        ];
                    }),
                ];
            }));

        return $activities;
    }

    #[Computed]
    public function renderers()
    {
        return ActivityLog::getItems($this->subject::modelClass());
    }

    #[Computed]
    public function userAvatar(): string
    {
        return Filament::getUserAvatarUrl(Filament::auth()->user());
    }

    public function getAvatarUrl(string $email): string
    {
        return Filament::getDefaultAvatarProvider()::generateGravatarUrl($email, size: 200);
    }

    public function render()
    {
        return view('lunarpanel::livewire.components.activity-log-feed');
    }
}
