# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## [Unreleased]

### Fixed

- When translating an attribute, if we can't find a translation we default to whatever the FieldType gives back instead of erroring.
- `TranslatedText` fieldtype now implements `JsonSerializable` interface ([#50](https://github.com/getcandy/getcandy/issues/50))
- When translating an attribute, if we can't find a translation we default to whatever the FieldType gives back instead of erroring.

### Added

- Created the `AttributeManifest` class so dev's can add their own attributable classes.
- Created the `FieldTypeManifest` class so dev's can add custom FieldTypes to the store.
- Added a new `default` column to the `tax_classes` table.
- Added `customer_id` to orders so an order has a 1:1 relation to a customer.

### Added

- Created the `AttributeManifest` class so dev's can add their own attributable classes.
- Created the `FieldTypeManifest` class so dev's can add custom FieldTypes to the store.

### Changed

- Models that have channels now implement `start_at` and `end_at` columns. This replaces the previous `published_at` column.

## 2.0-beta5 - 2022-01-10

### Fixes

- Added check on customers for when using MySQL search driver to prevent undefined columns [#40](https://github.com/getcandy/getcandy/issues/40)

### Changed

- Changed `https` to `http` on country import due to issues with local environment CA installations.

## 2.0-beta4 - 2022-01-07

### Added
- Made customers searchable via Scout
- Added Addresses relationship to the customer model

### Changed
- Customers `meta` column now uses Laravel's `casts` property and is cast to an object.

### Fixes
- Fixes [Issue 24](https://github.com/getcandy/getcandy/issues/24) where URL relationship is `elements` when it should be `element`
- Fixed an issue where `now()->year` would return an int on single digit months, but we need to have a leading zero.

[View Changes](https://github.com/getcandy/core/compare/2.0-beta3...2.0-beta4)

## 2.0-beta3 - 2021-12-24

### Fixes
- Fixed and issue where the meilisearch set up wasn't creating the indexes it needed if they didn't exist.

[View Changes](https://github.com/getcandy/core/compare/2.0-beta2...2.0-beta3)

## 2.0-beta2 - 2021-12-23

### Added
- Added a default collection group
### Changes
- Install command no longer publishes hub assets
### Fixes
- Default currency has `enabled` set to true.

[View Changes](https://github.com/getcandy/core/compare/2.0-beta...2.0-beta2)

## 2.0-beta - 2021-12-22

Initial release.
