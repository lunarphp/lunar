<?php

namespace Lunar\Base\DataTransferObjects;

class StockInfo
{
    /**
     * @param int $stock
     * @param int $backorder
     * @param StockLocationInfo[] $locations
     */
    public function __construct(
        public int $stock = 0,
        public int $backorder = 0,
        public array $locations = []
    ) {
        //
    }
}
