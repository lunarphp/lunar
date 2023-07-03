<?php

namespace Lunar\Base\DataTransferObjects;

class StockLocationInfo
{
    public function __construct(
        public int $stock = 0,
        public int $backorder = 0,
        public array $meta = []
    ) {
        //
    }
}
