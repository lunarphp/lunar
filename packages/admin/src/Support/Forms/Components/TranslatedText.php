<?php

namespace Lunar\Admin\Support\Forms\Components;

use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Lunar\Models\Language;
use Spatie\LaravelBlink\BlinkFacade as Blink;

class TranslatedText extends TextInput
{
    protected string $view = 'lunarpanel::forms.components.translated-text';

    public function getLanguages(): Collection
    {
        $key = 'lunarpanel_'.Str::snake(self::class);

        return Blink::once($key, function () {
            return Language::orderBy('default', 'desc')->get();
        });
    }

    public function getLanguageDefaults(): array
    {
        return $this->getLanguages()->mapWithKeys(fn ($language) => [$language->code => null])->toArray();
    }

    public function getDefaultLanguage(): Language
    {
        return Language::getDefault();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->default(static function (TranslatedText $component): array {
            return $component->getLanguageDefaults();
        });

        $this->afterStateHydrated(static function ($state, TranslatedText $component) {
            $defaults = $component->getLanguageDefaults();
            $defaultLang = $component->getDefaultLanguage()->code;

            foreach ($defaults as $language => $value) {
                $defaults[$language] = $state[$language] ?? $state[$defaultLang] ?? null;
            }

            $component->state($defaults);
        });

        $this->mutateDehydratedStateUsing(static function (TranslatedText $component, ?array $state) {
            return (object) $state;
        });
    }
}
