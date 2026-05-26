<?php

namespace Lunar\Core\Contracts;

use Lunar\Core\Drivers\SystemTaxDriver;

interface TaxManager
{
    /**
     * Create the system driver.
     *
     * @return SystemTaxDriver
     */
    public function createSystemDriver();

    /**
     * Return the default driver reference.
     *
     * @return string
     */
    public function getDefaultDriver();

    /**
     * Build the provider.
     *
     * @return TaxDriver
     */
    public function buildProvider();
}
