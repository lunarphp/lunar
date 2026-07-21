<?php

namespace Lunar\Panel\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Lunar\Core\Actions\Products\GenerateProductVariants;

/**
 * The option selection posted by the variant builder: shared options by
 * reference with the chosen value ids, exclusive options with their inline
 * name/value payloads. Deep ownership checks (shared flag, value membership)
 * live in the action, which validates before writing.
 */
class ProductOptionsGenerateRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'selections' => ['present', 'array', 'max:'.GenerateProductVariants::MAX_OPTIONS],
            'selections.*.type' => ['required', Rule::in(['shared', 'exclusive'])],
            'selections.*.id' => ['nullable', 'integer'],
            'selections.*.value_ids' => ['exclude_unless:selections.*.type,shared', 'required', 'array', 'min:1'],
            'selections.*.value_ids.*' => ['integer'],
            'selections.*.name' => ['exclude_unless:selections.*.type,exclusive', 'required'],
            'selections.*.values' => ['exclude_unless:selections.*.type,exclusive', 'required', 'array', 'min:1'],
            'selections.*.values.*.id' => ['nullable', 'integer'],
            'selections.*.values.*.name' => ['required'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function selections(): array
    {
        return $this->validated()['selections'];
    }
}
