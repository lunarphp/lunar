<?php

namespace Lunar\Filament\Forms\Components;

use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\Language;

class LanguageSelect extends Select
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('lunar-filament::forms/selectors.language.label'));
        $this->placeholder(__('lunar-filament::forms/selectors.language.placeholder'));
        $this->relationship('language', 'name');
        $this->preload();
    }

    /**
     * @return class-string<Model>
     */
    public function lunarModel(): string
    {
        return Language::class;
    }
}
