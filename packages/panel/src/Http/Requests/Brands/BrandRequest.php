<?php

namespace Lunar\Panel\Http\Requests\Brands;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Collection;
use Lunar\Core\States\Brand\Active;
use Lunar\Core\States\Brand\Draft;

/** Shared by the brand store and update endpoints, whose rules are identical. */
class BrandRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return static::rulesFor($this->route('brand'));
    }

    /**
     * The rule set, parameterised on the brand being edited so the drafts
     * layer can validate a commit payload with the same rules the update
     * endpoint applies. On create (null) the handle may be omitted — the
     * model generates one from the name.
     *
     * @return array<string, array<int, mixed>>
     */
    public static function rulesFor(?Brand $brand): array
    {
        $unique = Rule::unique((new Brand)->getTable(), 'handle');

        if ($brand) {
            $unique->ignore($brand->getKey());
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'handle' => [
                $brand ? 'required' : 'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                $unique,
            ],
            'status' => ['nullable', Rule::in([Active::$name, Draft::$name])],
            'short_description' => ['nullable', 'array'],
            'short_description.*' => ['nullable', 'string', 'max:65535'],
            'description' => ['nullable', 'array'],
            'description.*' => ['nullable', 'string', 'max:65535'],
            'collection_ids' => ['nullable', 'array'],
            'collection_ids.*' => ['integer', Rule::exists((new Collection)->getTable(), 'id')],
        ];
    }

    /** @return array<string, mixed> */
    public function brandAttributes(): array
    {
        return collect($this->validated())
            ->except('collection_ids')
            ->reject(fn (mixed $value, string $key) => $key === 'handle' && blank($value))
            ->reject(fn (mixed $value, string $key) => $key === 'status' && blank($value))
            ->all();
    }

    /**
     * The collections to sync, or null when the request left membership untouched.
     *
     * @return ?array<int, int>
     */
    public function collectionIds(): ?array
    {
        return array_key_exists('collection_ids', $this->validated())
            ? array_map('intval', $this->validated()['collection_ids'] ?? [])
            : null;
    }
}
