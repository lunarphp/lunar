# Extending Attributes

You can add your own attribute field types to Lunar and control how they are rendered in the panel.

In order to render the form component from the attribute, it needs to be converted into a Filament form component and a suitable Livewire Synthesizer associated so it can be hydrated/dehydrated properly.

## Create the field

```php
use Lunar\FieldTypes\Text;

class CustomField extends Text
{
    // ...
}
```

## Create the field type

```php
use Lunar\Admin\Support\FieldTypes\BaseFieldType;

class CustomFieldType extends BaseFieldType
{
    protected static string $synthesizer = CustomFieldSynth::class;

    public static function getFilamentComponent(Attribute $attribute): Component
    {
        return TextInput::make($attribute->handle);
    }
}
```

## Adding settings

There may be additional settings you want your field to have, for example the Number field has `min` and `max` settings.
To add these fields, you will need to tell Filament how to render the inputs.

```php
class CustomFieldType extends BaseFieldType
{
    // ...
    
    public static function getConfigurationFields(): array
    {
        return [
            Grid::make(2)->schema([
                \Filament\Forms\Components\TextInput::make('min_length'),
                \Filament\Forms\Components\TextInput::make('max_length'),
            ]),
        ];
    }
}
```

These will when be stored in the `configuration` JSON column for the attribute. Which you can then access when you
render the field in the panel.

```php
use Lunar\Admin\Support\FieldTypes\BaseFieldType;

class CustomFieldType extends BaseFieldType
{
    // ...
    
    public static function getFilamentComponent(Attribute $attribute): Component
    {
        $min = (int) $attribute->configuration->get('min_length');
        $max = (int) $attribute->configuration->get('max_length');
        
        return TextInput::make($attribute->handle)->min($min)->max($max);
    }
}
```

## Create the Livewire Synthesizer

So Livewire knows how to hydrate/dehydrate the values provided to the field type when editing, we need to add a
Synthesizer. You can read more about Livewire Synthesizers and what they do
here: [https://livewire.laravel.com/docs/synthesizers](https://livewire.laravel.com/docs/synthesizers)

```php
<?php

use Lunar\Admin\Support\Synthesizers\AbstractFieldSynth;
use Lunar\FieldTypes\Text;

class CustomFieldSynth extends AbstractFieldSynth
{
    public static $key = 'lunar_custom_field_field';

    protected static $targetClass = CustomField::class;
}
```

## Register the field

The last step is the register the converter with the `AttributeData` facade. Usually this would be in your service
provider.

```php
use Lunar\Admin\Support\Facades\AttributeData;

AttributeData::registerFieldType(\App\FieldTypes\CustomField::class, \App\Panel\FieldTypes\CustomFieldType::class);
```
