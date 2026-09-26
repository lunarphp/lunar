<?php

namespace Lunar\Api\Admin\Resources\V1;

use Lunar\Api\Contracts\DescribesSchema;
use Lunar\Api\OpenApi\Schema;

/** The `POST /api-keys` body: the key plus the plaintext token that is never shown again. */
final class IssuedApiKey implements DescribesSchema
{
    public static function schema(): Schema
    {
        return Schema::allOf(
            Schema::ref(ApiKeyResource::class),
            Schema::object([
                'token' => Schema::string()->describe('The plaintext bearer token. Store it now; it is not retrievable later.'),
            ], required: ['token']),
        );
    }
}
