<?php

namespace Lunar\Panel\Http\Requests\Collections;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\CollectionGroup;
use Lunar\Core\States\Collection\Archived;
use Lunar\Core\States\Collection\Draft;
use Lunar\Core\States\Collection\Published;

/**
 * The minimal create payload: a default-locale name plus where the
 * collection sits (group, optional parent). Handle and slug generation ride
 * the core create actions; everything else is edited on the collection page.
 */
class CollectionStoreRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'collection_group_id' => ['required', 'integer', Rule::exists((new CollectionGroup)->getTable(), 'id')],
            'parent_id' => ['nullable', 'integer', Rule::exists((new Collection)->getTable(), 'id')],
            'status' => ['nullable', Rule::in([Published::$name, Draft::$name, Archived::$name])],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $parentId = $this->input('parent_id');

            if (! $parentId || $validator->errors()->isNotEmpty()) {
                return;
            }

            $parent = Collection::find($parentId);

            if ($parent && (int) $parent->collection_group_id !== (int) $this->input('collection_group_id')) {
                $validator->errors()->add('parent_id', __('panel::collections.validation_parent_group'));
            }
        });
    }

    public function parent(): ?Collection
    {
        $parentId = $this->validated()['parent_id'] ?? null;

        return $parentId ? Collection::find($parentId) : null;
    }

    /** @return array<string, mixed> */
    public function collectionAttributes(): array
    {
        return blank($this->validated()['status'] ?? null)
            ? []
            : ['status' => $this->validated()['status']];
    }
}
