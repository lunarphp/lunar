<?php

namespace Lunar\Shipping\Resolvers;

use Illuminate\Support\Collection;

class PostcodeResolver
{
    public function getParts($postcode): Collection
    {
        $postcode = str_replace(' ', '', strtoupper($postcode));

        return collect([
            $postcode,
            rtrim(substr($postcode, 0, -3), 'a..zA..Z'),
            rtrim(substr($postcode, 0, -3), 'a..zA..Z').'*',
            rtrim($postcode, '0..9'),
            rtrim($postcode, '0..9').'*',
            substr($postcode, 0, 2),
            substr($postcode, 0, 2).'*',
            substr($postcode, 0, 1),
        ])->filter()->unique()->values();
    }
}
