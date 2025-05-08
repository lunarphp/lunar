<?php

namespace Lunar\Tests\Admin\Stubs\Filament\Extensions;

use Lunar\Admin\Support\Extending\ViewPageExtension;

class ExtensionB extends ViewPageExtension
{
    public function headerActions(array $actions): array
    {
        return [
            ...$actions,
        ];
    }
}
