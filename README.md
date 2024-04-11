<p align="center"><a href="https://lunarphp.io/" target="_blank"><picture><source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/lunarphp/art/main/lunar-logo-dark.svg"><img alt="Lunar" width="200" src="https://raw.githubusercontent.com/lunarphp/art/main/lunar-logo.svg"></picture></a></p>

> [!CAUTION]
> This branch is Work In Progress and should not be considered production-ready.


[Lunar](https://lunarphp.io) is a set of Laravel packages that bring functionality akin to Shopify and other e-commerce platforms to 
Laravel. You have complete freedom to create your own storefront(s), but we've already done the hard work for you in 
the backend.

This repository serves as a monorepo for the main packages that make up Lunar.

## Requirements

- PHP >= 8.2
- Laravel 10 / Laravel 11
- MySQL 8.0+ / PostgreSQL 9.2+

## Documentation

- [v1.0 documentation](https://docs-v1.lunarphp.io/)

## Contribution

- Bug reports should be submitted as a new Github issue
- Enhancements should [be in discussions](https://github.com/lunarphp/lunar/discussions/new?category=enhancements)
- Feature requests should [be in discussions](https://github.com/lunarphp/lunar/discussions/new?category=feature-requests)

## Community

- [Join our discord server](https://discord.gg/v6qVWaf) and chat to the developers and people using Lunar.
- [We have a roadmap](https://github.com/orgs/lunarphp/projects/8) where we will be detailing which features are next.

## Packages in this monorepo

### Admin panel

The admin panel is provided to enable you to manage your store via a modern interface. You can manage all aspects of 
your store including products, orders, staff members etc. It's built using Filament and can be extended to meet each of 
your stores requirements.

### Core

The core Lunar package, this provides all the things needed for your store to function. This is where all the models, 
actions and utilities live and is required by the admin hub.

---

## License

Lunar is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
