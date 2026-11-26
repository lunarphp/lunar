<?php

namespace Lunar\Panel\Http\Requests\Discounts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Lunar\Core\Models\Coupon;

/** Adds one hand-typed code to a coupon discount. */
class DiscountCouponRequest extends FormRequest
{
    /**
     * Codes are stored uppercase, so the uniqueness check has to compare uppercase too.
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('code'))) {
            $this->merge(['code' => Str::upper(trim($this->input('code')))]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:255', Rule::unique((new Coupon)->getTable(), 'code')],
            'max_uses' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
