<?php

namespace Lunar\Panel\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\Url;

/** Shared by the product URL store and update endpoints, whose rules are identical. */
class ProductUrlRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        // Slugs route the storefront globally per language, so uniqueness is
        // scoped to element type + language rather than to this product — the
        // same rule the Filament URL pages enforce.
        $unique = Rule::unique((new Url)->getTable(), 'slug')
            ->where('element_type', (new Product)->getMorphClass())
            ->where('language_id', $this->integer('language_id'));

        if ($url = $this->route('url')) {
            $unique->ignore($url->getKey());
        }

        return [
            'language_id' => ['required', 'integer', Rule::exists((new Language)->getTable(), 'id')],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $unique],
            'default' => ['nullable', 'boolean'],
        ];
    }
}
