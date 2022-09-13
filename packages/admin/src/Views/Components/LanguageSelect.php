<?php

namespace Lunar\Hub\Views\Components;

use Illuminate\View\Component;
use Lunar\Models\Language;

class LanguageSelect extends Component
{
    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.language-select', [
            'languages' => Language::all(),
        ]);
    }
}
