<?php

namespace GetCandy\Hub\DataTransferObjects;

class TableFilter
{
    public function __construct(
        public string $heading,
        public string $field
    ) {
        // ..
    }
}
