# Installation

::: warning
This admin panel requires Livewire v3. Due to this, it cannot be installed alongside the "old" Livewire admin panel.
:::

## Install The Package

In the root folder of your Laravel application, create a "packages" folder.

```sh
mkdir packages && cd packages
````

Add the "packages" folder to your `.gitignore` file so the folder is not committed to your Git repository.

```
...
/.idea
/.vscode
/packages // [!code focus]
```

Fork and then clone the [panel](https://github.com/lunarphp/panel) to the `packages` folder, e.g. `/packages/panel/`.

```sh
git clone https://github.com/YOUR-USERNAME/panel
````

Update your `composer.json` file similar to the following.

```json
"repositories": [{
    "type": "path",
    "url": "packages/*",
    "symlink": true
}],

"require": {
    "lunarphp/panel": "*",
}
````

Run `composer update` from your Laravel application's root directory and fingers crossed you're all up and running.

```sh
composer update
````

## Register The Panel

In your application's service provider add the following to the register method...

```php
public function register(): void
{
    LunarPanel::register(); // [!code focus]
```

You should now be able to access the panel at `https://<yoursite>/lunar`.
