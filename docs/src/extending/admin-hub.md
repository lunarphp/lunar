# Admin Hub

[[toc]]

## Overview

The admin hub is designed to be extended so you can add your own screens.

You should develop your additional functionality using Laravel Livewire using the same approach as the core admin hub screens.

## Adding to Menus

GetCandy uses dynamic menus in the UI which you can extend to add further links.

::: tip
Currently, only the side menu and settings menu are available to extend. But we will be adding further menus into the core editing screens soon.
:::

Here is an example of how you would add a new link to the side menu.

```php
use GetCandy\Hub\Facades\Menu;

$slot = Menu::slot('sidebar');

$slot->addItem(function ($item) {
    $item->name(
        __('menu.sidebar.tickets')
    )->handle('hub.tickets')
    ->route('hub.tickets.index')
    ->icon('ticket');
});
```

GetCandy comes with a collection of icons you can use in the Resources folder. If you wish to supply your own, simply use an SVG instead, e.g.

```php
->icon('<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.5" stroke="#9A9AA9" fill="none" stroke-linecap="round" stroke-linejoin="round">
  <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
  <line x1="15" y1="5" x2="15" y2="7" />
  <line x1="15" y1="11" x2="15" y2="13" />
  <line x1="15" y1="17" x2="15" y2="19" />
  <path d="M5 5h14a2 2 0 0 1 2 2v3a2 2 0 0 0 0 4v3a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-3a2 2 0 0 0 0 -4v-3a2 2 0 0 1 2 -2" />
</svg>');
```

## Slots

Slots allow you to add your own Livewire components to the screens around the hub. This is useful if you need to add extra forms or display certain data on pages like Product editing. Slots can be an extremely powerful addition to your store.

## Creating a Slot

A Slot is a Livewire component that implements the `AbstractSlot` interface. Here's a basic example of how this might look:

```php
<?php

namespace App\Slots;

use GetCandy\Hub\Slots\AbstractSlot;
use GetCandy\Hub\Slots\Traits\HubSlot;
use Livewire\Component;

class SeoSlot extends Component implements AbstractSlot
{
    use HubSlot;

    public static function getName()
    {
        return 'hub.product.slots.seo-slot';
    }

    public function getSlotHandle()
    {
        return 'seo-slot';
    }

    public function getSlotInitialValue()
    {
        return [];
    }

    public function getSlotPosition()
    {
        return 'top';
    }

    public function getSlotTitle()
    {
        return '';
    }

    public function updateSlotModel()
    {
    }

    public function handleSlotSave($model, $data)
    {
        $this->slotModel = $model;
    }

    public function render()
    {
        return view('path.to.component.view');
    }
}
```

### Available Methods

Aside from having all of Livewire's methods available, there are some additional methods you need to define for things to run smoothly.

#### `getName`

This is the name of the Livewire component and is referenced when rendering it i.e.

```php
@livewire($slot->getName())
```

When the Hub renders the component, we check for the existence of `hub` in the name to make sure the correct Middleware is applied without interfering with any components you may already have for your Storefront.

The name should be the same as how you've registered the component with Livewire:

```php
Livewire::component('hub.product.slots.seo-slot', SeoSlot::class);
```

#### `getSlotHandle`

This should be the unique handle for your Slot.

#### `getSlotInitialValue`

This method allows you to set any initial values on your slot before rendering.


#### `getSlotPosition`

Each page that supports slots will have different positions available where they can be placed. Return the position you want it to appear here.

#### `getSlotTitle`

Return the title for the slot.

#### `updateSlotModel`

This is called when the parent component of your Slot is bound to changes. e.g. if you have a slot on the product editing component, this will be called when the product is saved.

#### `handleSlotSave`

Called before `updateSlotModel` so you can save any data you need to the database.

#### `render`

Standard Livewire method to render the component view.


### Registering the Slot

Once you've created your Slot, you need to tell GetCandy where it should go, you can do this in your ServiceProvider.

```php
Slot::register('product.show', SeoSlot::class);
```

## Available Slots


### Products

#### `product.show`

Rendered on the product editing screen

##### Positions

|Position|Description
|:-|:-|
|`top`|Displayed at the top of the product editing sections
|`bottom`|Displayed at the bottom of the product editing sections

#### `product.create`

Rendered on the product creation screen

##### Positions

|Position|Description
|:-|:-|
|`top`|Displayed at the top of the product creation sections
|`bottom`|Displayed at the bottom of the product creation sections

#### `product.all`

Rendered on the product creation screen

##### Positions

|Position|Description
|:-|:-|
|`top`|Displayed at the top of both the product creation and editing sections
|`bottom`|Displayed at the bottom of both the product creation and editing sections


#### `productvariant.show`

##### Positions

|Position|Description
|:-|:-|
|`top`|Displayed at the top of both the product variant editing sections
|`bottom`|Displayed at the bottom of both the product variant editing sections