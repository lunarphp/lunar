<?php

namespace Lunar\Panel\Http\Requests\Collections;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Lunar\Core\Models\CollectionGroup;

/** Shared by the collection-group store and update endpoints. */
class CollectionGroupRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $unique = Rule::unique((new CollectionGroup)->getTable(), 'handle');

        if ($group = $this->route('collectionGroup')) {
            $unique->ignore($group->getKey());
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'handle' => [
                $this->route('collectionGroup') ? 'required' : 'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                $unique,
            ],
        ];
    }

    /** @return array<string, mixed> */
    public function groupAttributes(): array
    {
        return collect($this->validated())
            ->reject(fn (mixed $value, string $key) => $key === 'handle' && blank($value))
            ->all();
    }
}
