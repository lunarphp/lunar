<?php

namespace Lunar\Api\Storefront\Resources\V1;

use Lunar\Api\OpenApi\Schema;
use Lunar\Api\Resources\Field;
use Lunar\Api\Resources\Resource;
use Lunar\Core\Models\Url;

class UrlResource extends Resource
{
    public static function type(): string
    {
        return 'urls';
    }

    public static function model(): string
    {
        return Url::class;
    }

    public static function description(): string
    {
        return 'A slug a product, collection or brand is reachable at, per language.';
    }

    public function fields(): array
    {
        return [
            Field::make('slug')->describe('The URL slug.'),
            Field::make('default')->describe('Whether this is the canonical URL for its language.'),
            Field::make('language', fn (Url $url) => $url->language?->code)->eagerLoad('language')
                ->type(Schema::string()->nullable())->describe('Language code the URL belongs to.'),
        ];
    }
}
