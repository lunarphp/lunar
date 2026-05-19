<?php

namespace Lunar\Shipping\Exceptions;

use Lunar\Exceptions\LunarException;

class NoPostcodeResolverException extends LunarException
{
    public static function forCountry(string $iso2): self
    {
        return new self(sprintf(
            'No postcode resolver is registered that supports country [%s]. Register a resolver via Postcode::addResolver() or ensure the default PostcodeResolver remains registered.',
            $iso2
        ));
    }
}
