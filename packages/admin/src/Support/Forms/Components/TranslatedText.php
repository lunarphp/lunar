<?php

namespace Lunar\Admin\Support\Forms\Components;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Collection;
use Lunar\Models\Language;

class TranslatedText extends TextInput
{
    protected string $view = 'lunarpanel::forms.components.translated-text';

    public $expanded = false;

    public Language $defaultLanguage;

    public $richtext = false;

    public array $components = [];

    public Collection $languages;

    public function setUp(): void
    {
        parent::setUp();

        $this->languages = Language::orderBy('default', 'desc')->get();

        foreach ($this->getLanguages() as $lang) {
            $this->components[] = $this->getRichtext() ?
              TranslatedRichEditor::make($lang->code) :
              TranslatedTextInput::make($lang->code)->required($lang == $this->getDefaultLanguage());
        }

        $this->childComponents($this->components);
    }

    public function richtext(bool $richtext): static
    {
        $this->richtext = $richtext;

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

    public function getDefaultLanguage()
    {
        return $this->languages->first(fn ($lang) => $lang->default);
    }

    public function getMoreLanguages()
    {
        return $this->languages->filter(fn ($lang) => ! $lang->default);
    }

    public function getLanguageDefaults(): array
    {
        return $this->getLanguages()->mapWithKeys(fn ($language) => [$language->code => null])->toArray();
    }

    public function getLanguages()
    {
        return $this->languages;
    }

    public function getComponentByLanguage(Language $language): Component
    {
        return collect($this->getChildComponentContainer()->getComponents())
            ->filter(static fn ($component): bool => $component->getName() == $language->code)->first();
    }
}
