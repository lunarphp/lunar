<?php

namespace Lunar\Admin\Support\Forms\Components;

use Filament\Forms\Components\TextInput;
use Lunar\Models\Language;

class TranslatedText extends TextInput
{
    protected string $view = 'lunarpanel::forms.components.translated-text';

    /**
     * Whether translations should be expanded.
     *
     * @var bool
     */
    public $expanded = false;

    public $default;

    public $languages;

     /**
     * Create a new instance of the component.
     *
     * @param  bool  $expanded
     */
    public function setUp() : void
    {
        $languages = Language::orderBy('created_at', 'asc')->get();

        $this->languages = $languages->filter(fn ($lang) => ! $lang->default);
        $this->default = $languages->first(fn ($lang) => $lang->default);
    }

    public function getExpanded()
    {
        return $this->expanded;
    }

    public function getDefault()
    {
        // TODO: blink + sort
        return $this->default;
    }
 
    public function getLanguages()
    {
        // TODO: blink + sort
        return $this->languages;
    }
}
