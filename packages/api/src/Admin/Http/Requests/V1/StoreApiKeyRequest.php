<?php

namespace Lunar\Api\Admin\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Lunar\Api\Admin\Auth\Abilities;
use Lunar\Core\Auth\Manifest;
use Lunar\Core\Models\Staff;

class StoreApiKeyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(Manifest $manifest): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'abilities' => ['required', 'array', 'min:1'],
            'abilities.*' => ['string', Rule::in(Abilities::all($manifest))],
            'staff_id' => ['sometimes', 'nullable', 'string', Rule::exists((new Staff)->getTable(), 'public_id')],
            'expires_at' => ['sometimes', 'nullable', 'date', 'after:now'],
        ];
    }
}
