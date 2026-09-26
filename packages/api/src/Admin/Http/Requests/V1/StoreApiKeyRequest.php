<?php

namespace Lunar\Api\Admin\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Lunar\Api\Admin\Auth\Abilities;
use Lunar\Api\Contracts\DescribesSchema;
use Lunar\Api\OpenApi\Schema;
use Lunar\Core\Auth\Manifest;
use Lunar\Core\Models\Staff;

/**
 * Described by hand for OpenAPI: the ability list comes from the permission
 * manifest, which reads the database, and the document must build without one.
 */
class StoreApiKeyRequest extends FormRequest implements DescribesSchema
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

    public static function schema(): Schema
    {
        return Schema::object([
            'name' => Schema::string()->describe('A label for the integration the key belongs to.'),
            'abilities' => Schema::array(Schema::string())
                ->describe('Permission handles to grant, from the staff permission manifest, or * for every permission. At least one.'),
            'staff_id' => Schema::string()->nullable()->describe('Public id of the staff member the key acts as. Omit for a service key.'),
            'expires_at' => Schema::dateTime()->nullable()->describe('When the key stops authenticating. Must be in the future; omit for no expiry.'),
        ], required: ['name', 'abilities']);
    }
}
