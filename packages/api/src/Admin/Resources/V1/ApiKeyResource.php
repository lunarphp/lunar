<?php

namespace Lunar\Api\Admin\Resources\V1;

use Lunar\Api\Models\ApiKey;
use Lunar\Api\Resources\Field;
use Lunar\Api\Resources\Filter;
use Lunar\Api\Resources\Resource;
use Lunar\Api\Resources\Sort;

class ApiKeyResource extends Resource
{
    public static function type(): string
    {
        return 'api-keys';
    }

    public static function model(): string
    {
        return ApiKey::class;
    }

    public function fields(): array
    {
        return [
            Field::make('name'),
            Field::make('token_prefix'),
            Field::make('abilities'),
            Field::make('staff_id', fn (ApiKey $key) => $key->staff?->public_id)->eagerLoad('staff'),
            Field::make('active', fn (ApiKey $key) => $key->isActive()),
            Field::make('last_used_at'),
            Field::make('expires_at'),
            Field::make('revoked_at'),
            Field::make('created_at'),
            Field::make('updated_at'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::exact('id', 'public_id'),
            Filter::column('name')->operators(['eq', 'like']),
            Filter::scope('active'),
        ];
    }

    public function sorts(): array
    {
        return [
            Sort::column('name'),
            Sort::column('created_at'),
            Sort::column('last_used_at'),
        ];
    }
}
