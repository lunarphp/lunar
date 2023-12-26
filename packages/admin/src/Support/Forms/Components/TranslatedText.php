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

    /**
     * Default language
     *
     * @var Language
     */
    public $default;

    /**
     * Languages exclude default language
     *
     * @var Language
     */
    public $languages;

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
        return $this->default;
    }
 
    public function getLanguages()
    {
        return $this->languages;
    }
}
