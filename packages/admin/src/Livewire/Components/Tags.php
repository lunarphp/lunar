<?php

namespace Lunar\Admin\Livewire\Components;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Facades\Filament;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Lunar\Admin\Support\ActivityLog\Concerns\CanDispatchActivityUpdated;
use Lunar\Facades\DB;
use Lunar\Models\Tag;

class Tags extends Component implements HasActions, HasForms
{
    use CanDispatchActivityUpdated;
    use InteractsWithActions;
    use InteractsWithForms;

    public array $tags = [];

    /**
     * The model to associate tags to.
     */
    #[Locked]
    public Model $taggable;

    /**
     * The search term for showing relevant available tags.
     */
    public ?string $searchTerm = null;

    /**
     * {@inheritDoc}
     */
    public function mount()
    {
        $this->tags = $this->taggable->tags->pluck('value')->toArray();
    }

    /**
     * Adds a new tag into the array.
     */
    public function addTag(string $tag): void
    {
        $this->tags[] = $tag;
    }

    public function saveAction()
    {
        return Action::make('save')
            ->action(fn () => $this->saveTags())
            ->after(function () {
                Notification::make()->title(__('lunarpanel::components.tags.notification.updated'))->success()->send();
            });
    }

    public function saveTags()
    {
        DB::transaction(function () {
            $databaseTags = Tag::whereIn('value', $this->tags)->get();

            $newTags = collect($this->tags)->filter(function ($value) use ($databaseTags) {
                return ! $databaseTags->pluck('value')->contains($value);
            });

            $currentTags = $this->taggable->tags()->pluck('value');

            $addedTags = collect($this->tags)->diff($currentTags);
            $removedTags = $currentTags->diff($this->tags);

            $this->taggable->tags()->sync($databaseTags);

            foreach ($newTags as $tag) {
                $this->taggable->tags()->create([
                    'value' => $tag,
                ]);
            }

            if ($addedTags->count() || $removedTags->count()) {
                activity()
                    ->causedBy(Filament::auth()->user())
                    ->performedOn($this->taggable)
                    ->event('tags-update')
                    ->withProperties([
                        'added' => $addedTags->all(),
                        'removed' => $removedTags->all(),
                    ])->log('tags-update');

                $this->dispatchActivityUpdated();
            }
        });
    }

    /**
     * Return the available tags based on search.
     */
    #[Computed]
    public function availableTags(): Collection
    {
        $tagTable = (new Tag)->getTable();

        $search = trim($this->searchTerm);

        if (! $search) {
            return collect();
        }

        return DB::connection(config('lunar.database.connection'))
            ->table(
                config('lunar.database.table_prefix').'taggables'
            )
            ->join($tagTable, 'tag_id', '=', "{$tagTable}.id")
            ->whereTaggableType(
                $this->taggable->getMorphClass()
            )
            ->limit(10)
            ->distinct()
            ->where('value', 'LIKE', "%{$search}%")
            ->pluck('value')
            ->filter(function ($value) {
                return ! in_array($value, $this->tags);
            });
    }

    public function render()
    {
        return view('lunarpanel::livewire.components.tags');
    }
}
