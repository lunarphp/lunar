<?php

namespace Lunar\Base;

use Lunar\Drivers\SimpleStockDriver;

interface StockManagerInterface
{
    /**
     * Create the simple stock driver.
     *
     * @return SimpleStockDriver
     */
    public function createSimpleDriver();

    /**
     * Return the default driver reference.
     *
     * @return string
     */
    public function getDefaultDriver();

    /**
     * Build the provider.
     *
     * @return StockDriver
     */
    public function buildProvider();
}
