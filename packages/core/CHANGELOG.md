# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## [Unreleased]

### Added

- Added `active` scope to the `Cart` model for carts that do not have an order associated.

### Fixed

- When a user logs in, the `CartSessionAuthListener` will now check for an active cart, rather than just grabbing the latest. ([#186](https://github.com/getcandy/getcandy/issues/186))

## 2.0-beta10 - 2022-02-18

### Added

- Added support for Laravel 9
- Added new `TaxBreakdown` and `TaxBreakdownAmount` Data Transfer Objects. ([#173](https://github.com/getcandy/getcandy/issues/173))
- Added `setCartLine` method to the system tax driver. ([#173](https://github.com/getcandy/getcandy/issues/173))
- GetCandy will now automatically prefer multiple default addresses for shipping and billing. By [@nicolalazzaro](https://github.com/nicolalazzaro)

### Changed

- The `taxBreakdown` method on the tax driver now uses Data Transfer Objects. ([#173](https://github.com/getcandy/getcandy/issues/173))
- The `CalculateLine` action will now take in to account whether a unit price has already been set. By [@ryanmitchell](https://github.com/ryanmitchell)

### Fixed

- GetCandy will now register it's bindings in the `boot` method of the service providers. By [@edcoreweb](https://github.com/edcoreweb)
- The Cart actions to calculate the totals will now use the `Taxes` facade correctly.

[View Changes](https://github.com/getcandy/core/compare/2.0-beta9...2.0-beta10)

## 2.0-beta9 - 2022-02-11

### Fixed

- If an attribute value is null it will be returned instead of the `{"en": null}` encoded string. ([#130](https://github.com/getcandy/getcandy/issues/130))
- When loading an exiting product with channel availabilty, the scheduling modal should no longer display without prompt.
- `Product` and `ProductOption` models will now take the Scout prefix config setting on the indexes.
- The `Text` fieldtype tolerance now accepts numeric values instead of just forcing strings.
- Translating an attribute via `->translateAttribute('name')` will now handle non translatable fields for consistency.
- When using the `getcandy:meilisearch:setup` only indexes available within the app should be affected.
- When installing GetCandy the correct configuration or rich text fields should now be applied. By ([@KKSzymanowski](https://github.com/KKSzymanowski))

### Added

- Added `Taxes` facade to ensure the `TaxManager` can be easily extended. ([#129](https://github.com/getcandy/getcandy/issues/129))
- `collections` relationship has been added to the `Product` model. By ([@poppabear8883](https://github.com/poppabear8883))
- GetCandy's models now support have added macro support. By ([@edcoreweb](https://github.com/edcoreweb))

### Changed

- Instead of assuming `$user->id` we know use `$user->getKey()`. By ([@ryanmitchell](https://github.com/ryanmitchell))
- Big maintainence update to the Docblocks across the codebase to help with IDE support. By ([@KKSzymanowski](https://github.com/KKSzymanowski))

[View Changes](https://github.com/getcandy/core/compare/2.0-beta8...2.0-beta9)

## 2.0-beta8 - 2022-02-01

### Fixed

- Added missing `HasTranslations` trait to `ProductVariant` model.

### Changed

- When adding addresses via the `CartManager` we update any existing addresses rather than removing and re adding.
- When generating handles for models, we have moved to a forced `snake` case. i.e. `some_handle`. By [@itcyborg](https://github.com/itcyborg)

### Added

- Added `scopeDefault` to the `Url` model.
- Added a new `defaultUrl` relationship to models using the `HasUrls` trait.
- Added new `PricingManager` class to deal with price fetching on purchasable objects.
- Allow hub path to be configurable in `system` config. By ([@ryanmitchell](https://github.com/ryanmitchell))

[View Changes](https://github.com/getcandy/core/compare/2.0-beta7...2.0-beta8)

## 2.0-beta7 - 2022-01-19

### Fixed

- When translating an attribute, if we can't find a translation, we default to whatever the FieldType gives back instead of erroring.
- `TranslatedText` fieldtype now implements `JsonSerializable` interface ([#50](https://github.com/getcandy/getcandy/issues/50)).
- Core tests now use the correct `User` model stub when running. By [@joelwmale](https://github.com/joelwmale)
- When creating a product, prices were being added twice. This should be resolved.
- When adding a cart line, meta fields were causing lines to be duplicated, this should be resolved.

### Changed

- Models that have channels now implement `start_at` and `end_at` columns. This replaces the previous `published_at` column.
- Laravel UI modal components removed.
- Description attribute is no longer `required` or a `system` attribute on install.

### Added

- Added a new `default` column to the `tax_classes` table.
- Added `customer_id` to orders so an order has a 1:1 relation to a customer. ([#73](https://github.com/getcandy/getcandy/issues/73)).
- Created the `AttributeManifest` class so dev's can add their own attributable classes.
- Created the `FieldTypeManifest` class so dev's can add custom FieldTypes to the store.
- Added `$table->userForeignKey()` macro for migrations that create foreign keys which reference a user id.

[View Changes](https://github.com/getcandy/core/compare/2.0-beta5...2.0-beta7)

## 2.0-beta5 - 2022-01-10

### Fixed

- Added check on customers for when using MySQL search driver to prevent undefined columns [#40](https://github.com/getcandy/getcandy/issues/40).

### Changed

- Changed `https` to `http` on country import due to issues with local environment CA installations.

## 2.0-beta4 - 2022-01-07

### Fixed

- Fixes [Issue 24](https://github.com/getcandy/getcandy/issues/24) where URL relationship is `elements` when it should be `element`.
- Fixed an issue where `now()->year` would return an int on single digit months, but we need to have a leading zero.

### Changed

- Customers `meta` column now uses Laravel's `casts` property and is cast to an object.

### Added

- Made customers searchable via Scout.
- Added addresses relationship to the customer model.

[View Changes](https://github.com/getcandy/core/compare/2.0-beta3...2.0-beta4)

## 2.0-beta3 - 2021-12-24

### Fixed

- Fixed and issue where the meilisearch set up wasn't creating the indexes it needed if they didn't exist.

[View Changes](https://github.com/getcandy/core/compare/2.0-beta2...2.0-beta3)

## 2.0-beta2 - 2021-12-23

### Fixed
- Default currency has `enabled` set to true.

### Changed
- Install command no longer publishes hub assets

### Added
- Added a default `CollectionGroup`.

[View Changes](https://github.com/getcandy/core/compare/2.0-beta...2.0-beta2)

## 2.0-beta - 2021-12-22

Initial release.
