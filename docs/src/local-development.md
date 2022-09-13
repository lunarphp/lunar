# Setting Up Lunar For Local Development

[[toc]]

## Overview

This guide is here to help you set-up Lunar locally so you can contribute to the core and admin hub.

## Before your start

You will need a Laravel application to run Lunar in. You can either use a fresh install of [Laravel](https://laravel.com/docs/9.x/installation) or the [GetCandy demo store](https://github.com/lunarphp/demo-store).

## Set-Up

In the root folder of your Laravel application, create a "packages" folder.

```sh
mkdir packages && cd packages
````

Add the "packages" folder to your `.gitignore` file so the folder is not committed to your Git repository.

```
...
/.idea
/.vscode
/packages
```

Fork and then clone the [monorepo](https://github.com/lunarphp/lunar) to the `packages` folder, e.g. `/packages/lunar/`.

```sh
git clone https://github.com/YOUR-USERNAME/lunar
````

Update your `composer.json` file similar to the following. Note we are targeting `lunar/lunar` and not `lunar/admin`.

```json
    "repositories": [{
        "type": "path",
        "url": "packages/*",
        "symlink": true
    }],

    "require": {
        "lunar/lunar": "dev-main",
    }
````

Run `composer update` from your Laravel application's root directory and fingers crossed you're all up and running,. 

```sh
composer update
````

## Done
You can now follow the Lunar installation process and start contributing.
