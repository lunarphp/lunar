<?php

namespace Lunar\Core\Contracts;

interface PaymentManager
{
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
