<?php

namespace Lunar\Panel\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Lunar\Core\Models\Tag;

/** Shared by the tag store and update endpoints, whose rules are identical bar the unique scope. */
class TagRequest extends FormRequest
{
    /** Tag values are upper-cased by the model, so validate against the stored form. */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('value'))) {
            $this->merge(['value' => Str::upper(trim($this->input('value')))]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var Tag|null $tag */
        $tag = $this->route('tag');

        return [
            'value' => [
                'required', 'string', 'max:255',
                Rule::unique(Tag::class, 'value')->ignore($tag?->id),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function tagAttributes(): array
    {
        return ['value' => $this->validated()['value']];
    }
}
