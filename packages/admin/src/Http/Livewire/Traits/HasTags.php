<?php

namespace GetCandy\Hub\Http\Livewire\Traits;

trait HasTags
{
    /**
     * Array of tags to assign.
     *
     * @var array
     */
    public array $tags = [];

    public function mountHasTags()
    {
        $this->tags = $this->existingTags;
    }

    /**
     * Computed property for existing tags.
     *
     * @return array
     */
    abstract public function getExistingTagsProperty(): array;
}
