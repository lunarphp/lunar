<?php

namespace Lunar\LivewireTables\Components\Concerns;

use Illuminate\Support\Collection;

trait HasTranslations
{
    public $translationNamespace = '';

    public $translationPath = '';

    public function translate($key, $subPath = null)
    {
        $path = $key;

        if ($subPath) {
            $path = "tables.{$subPath}.{$key}";
        }

        if ($namespace = config('livewire-tables.translate_namespace')) {
            $path = "{$namespace}::{$path}";
        }

        return __($path);
    }
}
