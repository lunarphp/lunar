<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Pricing\Fixture\RetypeFormatterStyleParamRector;

class Unrelated
{
    // Not a formatter method.
    public function render(string $formatterStyle = 'currency'): string
    {
        return $formatterStyle;
    }

    // Already an int.
    public function formatted(int $formatterStyle = 1): string
    {
        return (string) $formatterStyle;
    }

    // A differently-named string param on a formatter method stays put.
    public function unitFormatted(string $locale = 'en'): string
    {
        return $locale;
    }
}
