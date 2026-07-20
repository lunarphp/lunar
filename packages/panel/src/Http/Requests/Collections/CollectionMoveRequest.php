<?php

namespace Lunar\Panel\Http\Requests\Collections;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\CollectionGroup;

/**
 * A hierarchy move: the destination group and the parent to nest under
 * (null for root level). Cycle and cross-group guards live in the core
 * MovesCollection action.
 */
class CollectionMoveRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'collection_group_id' => ['required', 'integer', Rule::exists((new CollectionGroup)->getTable(), 'id')],
            'parent_id' => ['nullable', 'integer', Rule::exists((new Collection)->getTable(), 'id')],
        ];
    }

    public function group(): CollectionGroup
    {
        return CollectionGroup::query()->findOrFail($this->validated()['collection_group_id']);
    }

    public function parent(): ?Collection
    {
        $parentId = $this->validated()['parent_id'] ?? null;

        return $parentId ? Collection::query()->findOrFail($parentId) : null;
    }
}
