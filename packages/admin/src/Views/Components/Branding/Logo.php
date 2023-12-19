<?php

namespace Lunar\Hub\Views\Components\Branding;

use Illuminate\Support\Str;
use Illuminate\View\Component;

class Logo extends Component
{
    /**
     * Determine if we should display only the logo icon.
     */
    public bool $iconOnly;

    /**
     * Initialise the component.
     */
    public function __construct(bool $iconOnly = false)
    {
        $this->iconOnly = $iconOnly;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.branding.logo', [
            'reference' => Str::random(),
        ]);
    }
}
