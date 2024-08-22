<?php

namespace Lunar\Admin\Support\Forms\Components;

use Closure;
use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Collection;
use Lunar\Models\Language;

class TranslatedText extends TextInput
{
    protected string $view = 'lunarpanel::forms.components.translated-text';

    public bool $expanded = false;

    public bool $optionRichtext = false;

    protected ?array $richtextToolbarButtons = null;

    protected array $richtextDisableToolbarButtons = [];

    protected bool $richtextDisableAllToolbarButtons = false;

    protected Closure|null|string $richtextFileAttachmentsDisk = null;

    protected Closure|null|string $richtextFileAttachmentsDirectory = null;

    protected Closure|string $richtextFileAttachmentsVisibility = 'public';

    protected ?Closure $richtextGetUploadedAttachmentUrlUsing = null;

    protected ?Closure $richtextSaveUploadedFileAttachmentsUsing = null;

    protected bool $mergeExtraInputAttributes = false;

    public Language $defaultLanguage;

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
                $this->getTranslatedRichEditorComponent($lang->code) :
                $this->getTranslatedTextComponent($lang->code)
            )
        );
    }

    protected function getTranslatedRichEditorComponent(string $langCode): TranslatedRichEditor
    {
        $component = TranslatedRichEditor::make($langCode)
            ->statePath($langCode)
            ->disableAllToolbarButtons($this->richtextDisableAllToolbarButtons)
            ->fileAttachmentsVisibility($this->richtextFileAttachmentsVisibility)
            ->fileAttachmentsDirectory($this->richtextFileAttachmentsDirectory)
            ->fileAttachmentsDisk($this->richtextFileAttachmentsDisk)
            ->getUploadedAttachmentUrlUsing($this->richtextGetUploadedAttachmentUrlUsing)
            ->saveUploadedFileAttachmentsUsing($this->richtextSaveUploadedFileAttachmentsUsing);

        if (! empty($this->richtextToolbarButtons)) {
            $component->disableToolbarButtons($this->richtextToolbarButtons);
        }

        if ($this->richtextToolbarButtons !== null) {
            $component->toolbarButtons($this->richtextToolbarButtons);
        }

        return $this->prepareTranslatedTextComponent($component);
    }

    public function extraInputAttributes(array | Closure $attributes, bool $merge = false): static
    {
        $this->mergeExtraInputAttributes = $merge;

        if ($merge) {
            $this->extraInputAttributes[] = $attributes;
        } else {
            $this->extraInputAttributes = [$attributes];
        }

        return $this;
    }

    protected function getTranslatedTextComponent(string $langCode): TranslatedTextInput
    {
        $component = TranslatedTextInput::make($langCode)
            ->statePath($langCode)
            ->telRegex($this->telRegex)
            ->step($this->step);

        if ($this->isEmail) {
            $component->email();
        }

        if ($this->isTel) {
            $component->tel();
        }

        if ($this->isUrl) {
            $component->url();
        }

        if ($this->isNumeric) {
            $component->numeric();
        }

        if ($this->step === 1) {
            $component->integer();
        }

        return $this->prepareTranslatedTextComponent($component);
    }

    protected function prepareTranslatedTextComponent(TranslatedTextInput|TranslatedRichEditor $component): TranslatedTextInput|TranslatedRichEditor
    {
        $component
            ->regex($this->regexPattern)
            ->minLength($this->minLength)
            ->maxLength($this->maxLength);

        if (!empty($this->extraInputAttributes)) {
            $component->extraInputAttributes($this->extraInputAttributes, $this->mergeExtraInputAttributes);
        }

        return $component;
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

    public function richtextToolbarButtons(array $buttons): static
    {
        $this->richtextToolbarButtons = $buttons;

        return $this;
    }

    public function richtextDisableToolbarButtons(array $buttons): static
    {
        $this->richtextDisableToolbarButtons = $buttons;

        return $this;
    }

    public function richtextDisableAllToolbarButtons(bool $condition = true): static
    {
        $this->richtextDisableAllToolbarButtons = $condition;

        return $this;
    }

    public function richtextFileAttachmentsDirectory(string | Closure | null $name): static
    {
        $this->richtextFileAttachmentsDirectory = $name;

        return $this;
    }

    public function getExpanded(): bool
    {
        return $this->expanded;
    }

    public function getDefaultLanguage(): Language
    {
        return $this->languages->first(fn ($lang) => $lang->default);
    }

    public function getMoreLanguages(): Collection
    {
        return $this->languages->filter(fn ($lang) => ! $lang->default);
    }

    public function getLanguageDefaults(): array
    {
        return $this->getLanguages()->mapWithKeys(fn ($language) => [$language->code => ''])->toArray();
    }

    public function getLanguages(): Collection
    {
        return $this->languages;
    }
}
