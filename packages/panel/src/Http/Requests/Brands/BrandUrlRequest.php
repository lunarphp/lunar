<?php

namespace Lunar\Panel\Http\Requests\Brands;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Url;

/** Shared by the brand URL store and update endpoints, whose rules are identical. */
class BrandUrlRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        // Slugs route the storefront globally per language, so uniqueness is
        // scoped to element type + language rather than to this brand — the
        // same rule the Filament URL pages enforce.
        $unique = Rule::unique((new Url)->getTable(), 'slug')
            ->where('element_type', (new Brand)->getMorphClass())
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
