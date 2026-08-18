<?php

namespace Lunar\Panel\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Lunar\Core\Models\Currency;

/** Shared by the currency store and update endpoints, whose rules are identical bar the code unique scope. */
class CurrencyRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var Currency|null $currency */
        $currency = $this->route('currency');

        return [
            'code' => [
                'required', 'string', 'size:3', 'alpha',
                Rule::unique(Currency::class, 'code')->ignore($currency?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'exchange_rate' => ['required', 'numeric', 'gt:0'],
            'decimal_places' => ['required', 'integer', 'between:0,4'],
            'enabled' => ['sometimes', 'boolean'],
            'default' => ['sometimes', 'boolean'],
            'sync_prices' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * The validated input shaped for the currency actions: code normalised to
     * uppercase, the booleans cast, and flags omitted entirely when not
     * supplied so an update leaves them untouched.
     *
     * @return array<string, mixed>
     */
    public function currencyAttributes(): array
    {
        $validated = $this->validated();

        $attributes = [
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'exchange_rate' => $validated['exchange_rate'],
            'decimal_places' => (int) $validated['decimal_places'],
        ];

        foreach (['enabled', 'default', 'sync_prices'] as $flag) {
            if (array_key_exists($flag, $validated)) {
                $attributes[$flag] = (bool) $validated[$flag];
            }
        }

        return $attributes;
    }
}
