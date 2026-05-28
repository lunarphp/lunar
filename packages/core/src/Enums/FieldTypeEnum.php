<?php

namespace Lunar\Core\Enums;

use Lunar\Core\Contracts\FieldType;
use Lunar\Core\FieldTypes\Dropdown;
use Lunar\Core\FieldTypes\File;
use Lunar\Core\FieldTypes\ListField;
use Lunar\Core\FieldTypes\Number;
use Lunar\Core\FieldTypes\Text;
use Lunar\Core\FieldTypes\Toggle;
use Lunar\Core\FieldTypes\TranslatedText;
use Lunar\Core\FieldTypes\Vimeo;
use Lunar\Core\FieldTypes\YouTube;

enum FieldTypeEnum: string
{
    case Text = 'text';
    case Number = 'number';
    case TranslatedText = 'translated_text';
    case Toggle = 'toggle';
    case Dropdown = 'dropdown';
    case ListField = 'list';
    case File = 'file';
    case Vimeo = 'vimeo';
    case YouTube = 'youtube';

    /**
     * Return the FieldType class backing this type.
     *
     * @return class-string<FieldType>
     */
    public function fieldTypeClass(): string
    {
        return match ($this) {
            self::Text => Text::class,
            self::Number => Number::class,
            self::TranslatedText => TranslatedText::class,
            self::Toggle => Toggle::class,
            self::Dropdown => Dropdown::class,
            self::ListField => ListField::class,
            self::File => File::class,
            self::Vimeo => Vimeo::class,
            self::YouTube => YouTube::class,
        };
    }
}
