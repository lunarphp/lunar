<?php

namespace Lunar\Pricing;

interface PriceFormatterInterface
{
    public function decimal(): float;

    public function unitDecimal(): float;

    public function formatted(): mixed;

    public function unitFormatted(): mixed;
}
