<?php

namespace GetCandy\Hub\Views\Components;

use GetCandy\Models\Language;
use Illuminate\View\Component;

class Translatable extends Component
{
    /**
     * Whether translations should be expanded.
     *
     * @var bool
     */
    public $expanded = false;

    /**
     * Create a new instance of the component.
     *
     * @param  bool  $expanded
     */
    public function __construct($expanded = false)
    {
        $this->expanded = $expanded;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        $languages = Language::get();

        return view('adminhub::components.translatable', [
            'default' => $languages->first(fn ($lang) => $lang->default),
            'languages' => $languages->filter(fn ($lang) => ! $lang->default),
        ]);
    }
}
