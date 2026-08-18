<?php

namespace Lunar\Panel\Http\Requests\Collections;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Lunar\Core\Models\Collection;
use Lunar\Core\States\Collection\Archived;
use Lunar\Core\States\Collection\Draft;
use Lunar\Core\States\Collection\Published;

/** Rules for the collection update endpoint and the drafts layer. */
class CollectionRequest extends FormRequest
{
    /** Product-sort tokens the core SortProducts action understands. */
    public const SORTS = ['custom', 'min_price:asc', 'min_price:desc', 'sku:asc', 'sku:desc'];

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return static::rulesFor($this->route('collection'));
    }

    /**
     * The rule set, parameterised on the collection being edited so the
     * drafts layer can validate a commit payload with the same rules the
     * update endpoint applies. The translated `name` map must carry at least
     * one non-blank value.
     *
     * @return array<string, array<int, mixed>>
     */
    public static function rulesFor(?Collection $collection): array
    {
        $unique = Rule::unique((new Collection)->getTable(), 'handle');

        if ($collection) {
            $unique->ignore($collection->getKey());
        }

        return [
            'name' => ['required', 'array', function (string $attribute, mixed $value, \Closure $fail) {
                if (! collect($value)->contains(fn (mixed $text) => is_string($text) && trim($text) !== '')) {
                    $fail(__('panel::collections.validation_name_required'));
                }
            }],
            'name.*' => ['nullable', 'string', 'max:255'],
            'handle' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                $unique,
            ],
            'status' => ['nullable', Rule::in([Published::$name, Draft::$name, Archived::$name])],
            'sort' => ['nullable', Rule::in(static::SORTS)],
            'short_description' => ['nullable', 'array'],
            'short_description.*' => ['nullable', 'string', 'max:65535'],
            'description' => ['nullable', 'array'],
            'description.*' => ['nullable', 'string', 'max:65535'],
        ];
    }

    /** @return array<string, mixed> */
    public function collectionAttributes(): array
    {
        return collect($this->validated())
            ->reject(fn (mixed $value, string $key) => in_array($key, ['status', 'sort'], true) && blank($value))
            ->all();
    }
}
