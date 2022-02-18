<?php

namespace GetCandy\Base\DataTransferObjects;

use Illuminate\Support\Collection;

class TaxBreakdown
{
    public function __construct(
        public ?Collection $amounts = null
    ) {
        $this->amounts = $amounts ?: collect();
    }

    /**
     * Add a tax breakdown amount.
     *
     * @param  TaxBreakdownAmount  $taxBreakdownAmount
     * @return void
     */
    public function addAmount(TaxBreakdownAmount $taxBreakdownAmount)
    {
        $this->amounts->push($taxBreakdownAmount);
    }
}
