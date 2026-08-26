<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Pricing\Fixture\RetypeFormatterStyleParamRector;

use NumberFormatter;

class CustomFormatter
{
    public function formatted(?string $locale = null, string $formatterStyle = NumberFormatter::CURRENCY): string
    {
        return '';
    }

    public function unitFormatted(?string $locale = null, string $formatterStyle = NumberFormatter::CURRENCY): string
    {
        return '';
    }
}

?>
-----
<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Pricing\Fixture\RetypeFormatterStyleParamRector;

use NumberFormatter;

class CustomFormatter
{
    public function formatted(?string $locale = null, int $formatterStyle = NumberFormatter::CURRENCY): string
    {
        return '';
    }

    public function unitFormatted(?string $locale = null, int $formatterStyle = NumberFormatter::CURRENCY): string
    {
        return '';
    }
}

?>
