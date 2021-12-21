<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://getcandy.io/getcandy_logo.svg" width="400"></a></p>

<p align="center">
<a href="https://packagist.org/packages/getcandy/candy-api"><img src="https://img.shields.io/packagist/dt/getcandy/candy-api" alt="Total Downloads"></a>
<img src="https://github.styleci.io/repos/390643018/shield?style=flat" alt="Style CI Badge">
<a href="https://packagist.org/packages/getcandy/candy-api"><img src="https://img.shields.io/packagist/v/getcandy/candy-api" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/getcandy/candy-api"><img src="https://img.shields.io/packagist/l/getcandy/candy-api" alt="License"></a>
</p>

## About GetCandy

GetCandy is an open source E-commerce platform which embraces Laravel as it's foundation and uses it to build a highly extensible, robust and feature rich application you can build any store on.

We put developers first and try to ensure your experience is as smooth as possible.

---

## Requirements
- PHP ^8.0
- Laravel 8+
- MySQL 5.7+ / PostgreSQL 9.2+
- exif PHP extension (on most systems it will be installed by default)
- GD PHP extension (used for image manipulation)

## Documentation

- [Full documentation](https://getcandy.io/docs) - Includes in-depth guides on everything GetCandy

## Installation

Install the package via composer.

```sh
composer require getcandy/getcandy
```

Run the install command

```sh
php artisan getcandy:install
```

This will take you through a set of questions to configure your GetCandy install. The process includes...

- Creating a default admin user (if required)
- Specifying the table prefix
- Seeding initial data
- Publishing config files
- Optionally, installing demo data
- Inviting you to star our repo on GitHub 😎

Congratulations! You've just installed GetCandy.

## Running seeders

If you're just trying GetCandy and would like to have some test data to work with, you can run our seeders.

Note: there isn't currently a function to automatically remove the demo data.

```sh
php artisan db:seed --class=GetCandy\\Database\\Seeders\\DemoSeeder
```

## Contributing

Thank you for considering contributing to GetCandy! The contribution guide can be found in the [GetCandy documentation](https://getcandy.io/docs).

## License

GetCandy is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
