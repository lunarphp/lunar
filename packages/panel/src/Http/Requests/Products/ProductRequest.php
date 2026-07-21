<?php

namespace Lunar\Panel\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductType;
use Lunar\Core\States\Product\Archived;
use Lunar\Core\States\Product\Draft;
use Lunar\Core\States\Product\Published;
use Lunar\Core\States\ProductType\Active;

/** Rules for the product update endpoint and the drafts layer. */
class ProductRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return static::rulesFor($this->route('product'));
    }

    /**
     * The rule set, parameterised on the product being edited so the drafts
     * layer can validate a commit payload with the same rules the update
     * endpoint applies. The translated `name` map must carry at least one
     * non-blank value; the product type must be active, except that a product
     * keeps its current type even after that type is drafted.
     *
     * @return array<string, array<int, mixed>>
     */
    public static function rulesFor(?Product $product): array
    {
        return [
            'name' => ['required', 'array', function (string $attribute, mixed $value, \Closure $fail) {
                if (! collect($value)->contains(fn (mixed $text) => is_string($text) && trim($text) !== '')) {
                    $fail(__('panel::products.validation_name_required'));
                }
            }],
            'name.*' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in([Published::$name, Draft::$name, Archived::$name])],
            'product_type_id' => ['nullable', 'integer', function (string $attribute, mixed $value, \Closure $fail) use ($product) {
                $allowed = ProductType::query()
                    ->whereKey($value)
                    ->where(function ($query) use ($product) {
                        $query->where('status', Active::$name);

                        if ($product) {
                            $query->orWhere('id', $product->product_type_id);
                        }
                    })
                    ->exists();

                if (! $allowed) {
                    $fail(__('panel::products.validation_product_type'));
                }
            }],
            'brand_id' => ['nullable', 'integer', Rule::exists((new Brand)->getTable(), 'id')],
            'short_description' => ['nullable', 'array'],
            'short_description.*' => ['nullable', 'string', 'max:65535'],
            'description' => ['nullable', 'array'],
            'description.*' => ['nullable', 'string', 'max:65535'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:255'],
            'collection_ids' => ['nullable', 'array'],
            'collection_ids.*' => ['integer', Rule::exists((new Collection)->getTable(), 'id')],
        ];
    }

    /** @return array<string, mixed> */
    public function productAttributes(): array
    {
        return collect($this->validated())
            ->except(['tags', 'collection_ids'])
            ->reject(fn (mixed $value, string $key) => in_array($key, ['status', 'product_type_id'], true) && blank($value))
            ->all();
    }

    /**
     * The tags to sync, or null when the request left them untouched.
     *
     * @return ?array<int, string>
     */
    public function tags(): ?array
    {
        return array_key_exists('tags', $this->validated())
            ? array_map('strval', $this->validated()['tags'] ?? [])
            : null;
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
