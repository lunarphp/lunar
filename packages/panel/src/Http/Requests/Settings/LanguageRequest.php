<?php

namespace Lunar\Panel\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Lunar\Core\Models\Language;

/** Shared by the language store and update endpoints, whose rules are identical bar the code unique scope. */
class LanguageRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var Language|null $language */
        $language = $this->route('language');

        return [
            'code' => [
                'required', 'string', 'max:12',
                Rule::unique(Language::class, 'code')->ignore($language?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'default' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * The validated input shaped for the language actions: the default flag
     * cast, and omitted entirely when not supplied so an update leaves it
     * untouched.
     *
     * @return array<string, mixed>
     */
    public function languageAttributes(): array
    {
        $validated = $this->validated();

        $attributes = [
            'code' => $validated['code'],
            'name' => $validated['name'],
        ];

        if (array_key_exists('default', $validated)) {
            $attributes['default'] = (bool) $validated['default'];
        }

        return $attributes;
    }
}
