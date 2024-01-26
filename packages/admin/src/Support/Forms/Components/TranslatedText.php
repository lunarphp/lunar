<?php

namespace Lunar\Admin\Support\Forms\Components;

use Closure;
use Illuminate\Support\Collection;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Lunar\Models\Language;

class TranslatedText extends TextInput
{
    protected string $view = 'lunarpanel::forms.components.translated-text';

    public $expanded = false;

    public Language $defaultLanguage;

    public $richtext = false;

    public array $subComponents = [];

    public Collection $languages;

    public function setUp(): void
    {
        parent::setUp();

        $this->languages = Language::orderBy('default', 'desc')->get();

        /**
         *  All subcomponent
         *  without attribute data : make('name.en')
         *  with attribute data : make('handle.en')
         */
        // $this->afterStateHydrated(static function ($state, TranslatedText $component) {
        //     $defaults = $component->getLanguageDefaults();

        //     foreach ($defaults as $language => $_) {
        //         $defaults[$language] = $state[$language] ?? null;
        //     }

        //     $component->state($defaults);
        // });

        // $this->rules([
        //     function (TranslatedText $component) {
        //         return function (string $attribute, $value, Closure $fail) use ($component) {
        //             $defaultLanguage = $component->getDefaultLanguage();

        //             if (blank($value[$defaultLanguage->code] ?? null)) {
        //                 $fail("The {$defaultLanguage->name} :attribute is required.");
        //             }
        //         };
        //     },
        // ], fn (TranslatedText $component) => $component->isRequired());

        // Use child component to instanciate RichEditor
        foreach ($this->getLanguages() as $lang) {
          $this->subComponents[] = TranslatedRichEditor::make($lang->code);
        }

        $this->childComponents($this->subComponents);
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

    public function getRichEditorComponent(Language $language): RichEditor
    {
        return collect($this->getChildComponentContainer()->getComponents())
            ->filter(static fn ($component): bool => $component->getName() == $language->code)->first();
    }
}
