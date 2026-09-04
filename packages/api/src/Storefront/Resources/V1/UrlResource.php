<?php

namespace Lunar\Api\Storefront\Resources\V1;

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

    public function fields(): array
    {
        return [
            Field::make('slug'),
            Field::make('default'),
            Field::make('language', fn (Url $url) => $url->language?->code)->eagerLoad('language'),
        ];
    }
}
