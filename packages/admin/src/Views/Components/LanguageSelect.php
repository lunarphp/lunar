<?php

namespace GetCandy\Hub\Views\Components;

use GetCandy\Models\Language;
use Illuminate\View\Component;

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
