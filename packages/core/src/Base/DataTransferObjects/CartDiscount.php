<?php

namespace GetCandy\Base\DataTransferObjects;

class CartDiscount
{
    public function __construct(
        public string $name,
        public string $identifier
    ) {
        //
    }
}
