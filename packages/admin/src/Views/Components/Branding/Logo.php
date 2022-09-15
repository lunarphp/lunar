<?php

namespace Lunar\Hub\Views\Components\Branding;

use Illuminate\View\Component;
use Illuminate\Support\Str;

class Logo extends Component
{
    /**
     * Determine if we should display only the logo icon.
     *
     * @var bool
     */
    public bool $iconOnly;

    /**
     * Initialise the component.
     *
     * @param  bool  $iconOnly
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
