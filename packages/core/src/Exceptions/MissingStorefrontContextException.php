<?php

namespace Lunar\Core\Exceptions;

class MissingStorefrontContextException extends LunarException
{
    public function __construct()
    {
        parent::__construct(
            'A price was requested without a storefront context. Pass one with using() or pricing($context), '.
            'or call StorefrontSession::useAsDefaultContext() for the request.'
        );
    }
}
