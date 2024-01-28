<?php

namespace Lunar\Admin\Support\Forms\Components;

use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Collection;
use Lunar\Models\Language;

class TranslatedText extends TextInput
{
    protected string $view = 'lunarpanel::forms.components.translated-text';

    public $expanded = false;

    public Language $defaultLanguage;

    public bool $optionRichtext = false;

    public Collection $components;

    public Collection $languages;

    public function setUp(): void
    {
        parent::setUp();

        $this->languages = Language::orderBy('default', 'desc')->get();

        $this->default(static function (TranslatedText $component): array {
            return $component->getLanguageDefaults();
        });
    }

    public function prepareChildComponents()
    {
        $this->components = collect(
            $this->getLanguages()->map(fn ($lang) => $this->getOptionRichtext() ?
                    TranslatedRichEditor::make($lang->code)->statePath($lang->code) :
                    TranslatedTextInput::make($lang->code)->statePath($lang->code)
            )
        );
    }

    public function prepareTranslateLocaleComponent(Component $component, string $locale)
    {
        $localeComponent = clone $component;

        $localeComponent->name($component->getName());

        $localeComponent->statePath($localeComponent->getName());

        $localeComponent->required($this->isRequired && $locale == $this->getDefaultLanguage()->code);

        return $localeComponent;
    }

    public function getComponentByLanguage(Language $language): ComponentContainer
    {
        $this->prepareChildComponents();

        return ComponentContainer::make($this->getLivewire())
            ->parentComponent($this)
            ->components(
                $this->components
                    ->filter(fn ($component): bool => $component->getName() == $language->code)
                    ->map(fn ($component) => $this->prepareTranslateLocaleComponent($component, $language->code))
                    ->all()
            )
            ->getClone();
    }

    public function optionRichtext(bool $optionRichtext): static
    {
        $this->optionRichtext = $optionRichtext;

        return $this;
    }

    public function getOptionRichtext(): bool
    {
        return $this->optionRichtext;
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
        return $this->getLanguages()->mapWithKeys(fn ($language) => [$language->code => ''])->toArray();
    }

    public function getLanguages()
    {
        return $this->languages;
    }
}
