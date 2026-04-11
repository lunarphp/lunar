<?php

namespace Lunar\Base\DataTransferObjects;

class RefundAuthorizationResult
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public bool $authorized = true,
        public ?string $message = null,
        public array $meta = [],
    ) {
        //
    }
}
