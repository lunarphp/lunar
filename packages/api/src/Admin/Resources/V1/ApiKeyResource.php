<?php

namespace Lunar\Api\Admin\Resources\V1;

use Lunar\Api\Models\ApiKey;
use Lunar\Api\OpenApi\Schema;
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

    public static function label(): string
    {
        return 'API keys';
    }

    public static function description(): string
    {
        return 'Credentials for the admin API. The token is returned once, when the key is issued; revoked keys stay listed for the audit trail.';
    }

    public function fields(): array
    {
        return [
            Field::make('name')->describe('A label for the integration the key belongs to.'),
            Field::make('token_prefix')->describe('The first characters of the token, to identify a key without exposing it.'),
            Field::make('abilities')->type(Schema::array(Schema::string()))->describe('Permission handles the key was granted, or * for every permission.'),
            Field::make('staff_id', fn (ApiKey $key) => $key->staff?->public_id)->eagerLoad('staff')
                ->type(Schema::string()->nullable())->describe('Public id of the staff member the key acts as, or null for a service key.'),
            Field::make('active', fn (ApiKey $key) => $key->isActive())
                ->type(Schema::boolean())->describe('Whether the key is neither revoked nor expired.'),
            Field::make('last_used_at')->nullable()->describe('When the key last authenticated a request.'),
            Field::make('expires_at')->nullable()->describe('When the key stops authenticating, or null for no expiry.'),
            Field::make('revoked_at')->nullable()->describe('When the key was revoked, or null while it is live.'),
            Field::make('created_at')->describe('When the key was issued.'),
            Field::make('updated_at')->describe('When the key was last changed.'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::exact('id', 'public_id')->describe('Match by public id.'),
            Filter::column('name')->operators(['eq', 'like'])->describe('Match by name.'),
            Filter::scope('active')->describe('Only keys that are neither revoked nor expired when true.'),
        ];
    }

    public function sorts(): array
    {
        return [
            Sort::column('name')->describe('Alphabetical by name.'),
            Sort::column('created_at')->describe('By issue time.'),
            Sort::column('last_used_at')->describe('By last use.'),
        ];
    }
}
