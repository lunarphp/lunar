<?php

namespace Lunar\Base\DataTransferObjects;

class StockInfo
{
    /**
     * @param  StockLocationInfo[]  $locations
     */
    public function __construct(
        public int $stock = 0,
        public int $backorder = 0,
        public array $locations = []
    ) {
        //
    }
}
