<?php

namespace Lunar\Hub\Http\Livewire\Components;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Lunar\Facades\DB;
use Livewire\Component;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Models\Tag;

class Tags extends Component
{
    use Notifies;

    /**
     * The tags to attach.
     *
     * @var string
     */
    public array $tags = [];

    /**
     * The model to associate tags to.
     *
     * @var Model
     */
    public Model $taggable;

    /**
     * The search term for showing relevant available tags.
     *
     * @var string|null
     */
    public ?string $searchTerm = null;

    /**
     * Whether independant saving should be enabled.
     *
     * @var type
     */
    public bool $independant = false;

    /**
     * {@inheritDoc}
     */
    public function mount()
    {
        $this->tags = $this->taggable->tags->pluck('value')->toArray();
    }

    /**
     * Adds a new tag into the array.
     *
     * @param  string  $tag
     * @return void
     */
    public function addTag($tag)
    {
        $this->tags[] = $tag;
        $this->searchTerm = null;
    }

    public function saveTags()
    {
        DB::transaction(function () {
            $databaseTags = Tag::whereIn('value', $this->tags)->get();

            $newTags = collect($this->tags)->filter(function ($value) use ($databaseTags) {
                return ! $databaseTags->pluck('value')->contains($value);
            });

            $this->taggable->tags()->sync($databaseTags);

            foreach ($newTags as $tag) {
                $this->taggable->tags()->create([
                    'value' => $tag,
                ]);
            }

            $this->taggable->touch();
        });
        $this->notify(
            __('adminhub::notifications.tags.updated')
        );
    }

    /**
     * Return the available tags based on search.
     *
     * @return Collection
     */
    public function getAvailableTagsProperty()
    {
        $tagTable = (new Tag)->getTable();

        if (! $this->searchTerm) {
            return collect();
        }

        return DB::connection(config('lunar.database.connection'))
        ->table(
            config('lunar.database.table_prefix').'taggables'
        )->join($tagTable, 'tag_id', '=', "{$tagTable}.id")
            ->whereTaggableType(
                $this->taggable->getMorphClass()
            )
            ->distinct()
            ->where('value', 'LIKE', "%{$this->searchTerm}%")
            ->pluck('value')
            ->filter(function ($value) {
                return ! in_array($value, $this->tags);
            });
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.tags')
            ->layout('adminhub::layouts.base');
    }
}
