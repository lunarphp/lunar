<?php

namespace Lunar\Admin\Support\Forms\Components;

use Filament\Forms\Components\RichEditor;
use Lunar\Models\Language;

class TranslatedText extends RichEditor
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
     * Is rich text ?
     *
     * @var bool richtext
     */
    public $richtext = false;

    /**
     * Is attribute data
     *
     * @var bool attributeData
     */
    public $attributeData = false;

    /**
     * Languages exclude default language
     *
     * @var Language
     */
    public $languages;

    public function setUp(): void
    {
        $languages = Language::orderBy('default', 'desc')->get();

        $this->languages = $languages->filter(fn ($lang) => ! $lang->default);

        $this->default = $languages->first(fn ($lang) => $lang->default);

        $this->default(static function (TranslatedText $component): array {
            return $component->getLanguageDefaults();
        });
    }

    public function richtext(bool $richtext): static
    {
        $this->richtext = $richtext;

        return $this;
    }

    public function attributeData(): static
    {
        $this->attributeData = true;

        return $this;
    }

    public function getRichtext()
    {
        return $this->richtext;
    }

    public function getExpanded()
    {
        return $this->expanded;
    }

    public function getDefault()
    {
        return $this->default;
    }

    public function getLanguageDefaults(): array
    {
        return $this->getLanguages()->mapWithKeys(fn ($language) => [$language->code => null])->toArray();
    }

    public function getLanguages()
    {
        return $this->languages;
    }
}
