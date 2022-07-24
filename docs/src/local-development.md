# Setting Up GetCandy For Local Development

[[toc]]

## Overview

This guide is here to help you set-up GetCandy locally so you can contribute to the core and admin hub.

## Before your start

You will need a Laravel application to run GetCandy in. You can either use a fresh install of [Laravel](https://laravel.com/docs/9.x/installation) or the [GetCandy demo store](https://github.com/getcandy/demo-store).

## Set-Up

In the root folder of your Laravel applcation, create a "packages" folder.

```sh
mkdir packages && cd packages
````

Add the "packages" folder to your `.gitignore` file so the folder is not commited to your Git repo.

```
...
/.idea
/.vscode
/packages
```

Fork and then clone the [monorepo](https://github.com/getcandy/getcandy) to the `packages` folder, e.g. `/packages/getcandy/`.

```sh
git clone https://github.com/YOUR-USERNAME/getcandy
````

Update your `composer.json` file similar to the following. Note we are targetting `getcandy/getcandy` and not `getcandy/admin`.

```json
    "repositories": [{
        "type": "path",
        "url": "packages/*",
        "symlink": true
    }],

    "require": {
        "getcandy/getcandy": "dev-main",
    }
````

Run `composer update` from your Laravel application's root directory and fingers crossed you're all up and running,. 

```sh
composer update
````

## Done
You can now follow the GetCandy installation process and start contributing.
