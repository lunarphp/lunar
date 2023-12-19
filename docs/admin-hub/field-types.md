# Field Types

## Overview

There will likely come a point where you need more field types than what Lunar offers in the core. We've made adding your own a breeze and it's the same process whether you want to add these to directly to your app or via an add on.


Lunar will load up available field types via the `AttributeManifest`. This is a singleton which houses all the field types you can use. We currently have a handful in the core, with more being added in the future. If you need a field type that's not here, speak with us first in case it's in the pipeline.

## Create Your Field Type Class

Each FieldType is it's own class which implements the `FieldType` interface. So start by creating this class:

```php
<?php

namespace App\FieldTypes;

use Lunar\Base\FieldType;

class ColourPicker implements FieldType
{
    //...
}
```

There are a number of methods you need to implement, which we'll go through here.

#### `getValue`

This will return the value from the `attribute_date` for display.

#### `setValue($value)`

The input value will be passed through here, so do any additional processing you need and then set the value property:

```php
public function setValue($value)
{
   if ($this->hex($value)) {
     $value = $this->convertToRgb($value);
   }

   $this->value = $value;
}
```

#### `getLabel`

Return the display label for the field type, used when displaying in the admin hub.

#### `getConfig`

Return an array of configuration, gets passed to the settings view and saved against the attribute when edited.


#### `getSettingsView`

Return the reference to the settings view. Used in the hub when editing an attribute of for field type.

#### `getView`

Return the reference to the view when editing in the hub. i.e. on the product edit screen.

### Configuration

This should include any additional configuration your FieldType might need. If you need to store extra information that requires user input in the settings. You need to add them to the `option` array like so:

```php
public function getConfig()
{
    return [
      'options' => [
        'available_colours' => 'nullable',
      ],
    ];
}
```

This will then tell the Livewire component to expect these fields for this FieldType and add the appropriate validation rules you specify.

### Settings View

The settings view is what staff will see when they are creating an attribute with this field type. Here you can define any extra configuration your field type may need when displayed for editing.

If your settings require input from the staff member, such as a text input or dropdown, these fields must be present in the config.

```php
public function getConfig()
{
    return [
      'options' => [
        'available_colours' => 'nullable',
      ],
    ];
}
```

We can this rig this up in our settings view:

```html
<div>
  <label>Available Colours</label>
  <input type="text" wire:model="attribute.configuration.available_colours" />
</div>
```

### Edit View

When a staff member is editing something such as a product, we need to be able to show the correct type of input for our field type.

In this view we have access to the `$field` variable, which contains everything we need for data binding.

```blade
{{ $field['signature'] }} // Contains the signature for wire:model
{{ $field['data'] }} // The current data taken from the `attribute_data` column
```

Using the above we might have a view that looks like this:

```blade
<input type="text" wire:model="{{ $field['signature'] }}" />
```

You will also have access to the `$language` variable, which is generally used on translated attributes. This will be the language code such as `en`

## Registering Your FieldType

Once you've made the FieldType, you need to register it. Typically this could done in a service provider:

```php
\Lunar\Facades\FieldTypeManifest::add(
  ColourFieldType::class
);
```

## Registering FieldType Assets

If you need to register styles or scripts, you can do so in a service provider. More on this topic in [registering assets](/admin-hub/assets).

```php
// Register compiled script
LunarHub::script('lunar-package', __DIR__.'/../dist/lunar-package.js');

// Register remote script
LunarHub::remoteScript('https://example.com/script.js');

// Register compiled styles
LunarHub::style('lunar-package', __DIR__.'/../dist/lunar-package.css');

// Register remote styles
LunarHub::remoteStyle('https://example.com/style.css');
```
