<?php

namespace Lunar\Admin\Support\Pages\Concerns;

use Illuminate\Contracts\Support\Htmlable;

trait ExtendsHeadings
{
    public function getDefaultHeading(): string
    {
        return $this->heading ?? $this->getTitle();
    }

    public function getHeading(): string|Htmlable
    {
        return $this->callLunarHook('heading', $this->getDefaultHeading());
    }

    public function getDefaultSubheading(): ?string
    {
        return $this->subheading;
    }

    public function getSubheading(): string|Htmlable|null
    {
        return $this->callLunarHook('subHeading', $this->getDefaultSubheading());
    }
}
