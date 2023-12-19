<?php

namespace Lunar\Hub\Http\Livewire\Traits;

trait HasTags
{
    /**
     * Array of tags to assign.
     */
    public array $tags = [];

    public function mountHasTags()
    {
        $this->tags = $this->existingTags;
    }

    /**
     * Computed property for existing tags.
     */
    abstract public function getExistingTagsProperty(): array;
}
