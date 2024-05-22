<?php

namespace Lunar\Admin\Support\FieldTypes;

use Filament\Forms\Components\Component;
use Lunar\Admin\Support\Forms\Components\Vimeo as VimeoInput;
use Lunar\Admin\Support\Synthesizers\VimeoSynth;
use Lunar\Models\Attribute;

class Vimeo extends BaseFieldType
{
    protected static string $synthesizer = VimeoSynth::class;

    public static function getFilamentComponent(Attribute $attribute): Component
    {
        return VimeoInput::make($attribute->handle)
            ->live(debounce: 200)
            ->helperText(
                $attribute->translate('description') ?? __('lunarpanel::components.forms.youtube.helperText')
            );
    }
}
