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

## Customising to Editing Screens

Currently there is no way to add additional fields or components to editing screens. However, we intend to look into adding a "slots" feature to enable just that. 
