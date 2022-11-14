# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## 0.1.4

### Fixed

- Pagination should now highlight the current page better
- Restoring a staff member has been fixed in the hub.

## 0.1.3

### Fixed

- Added missing translations for min,max inputs
- Removed redundant `$symbol` on price input.
- Fixed readable validation error messages
- Input groups now support `$errorIcon` to help UI issue when validation errors are present.
- Tiered pricing UI now displays correctly under headings
- Staff table can now save searches properly.
- Order status UI will now fallback on the original if it doesn't exist in config.
- The side menu should now display the correct menu items when variants aren't present.
- The `bcmul` function is now used when saving prices to avoid rounding issues.

## 0.1.2

### Fixed

- Call `validate` when creating a tax class before the DB transaction.
- Fix product thumbnail on dashboard.
- Fix issue with customer saving [#654](https://github.com/lunarphp/lunar/issues/654)
- Use @js directive instead of @JSON
- Fixed an issue where Spatie Media couldn't regenerate media transforms from within the hub in production.
- Fixed issues with product editing when disabling variants.
- Fixed issue where trying to add a saved search on product types has no effect.
- Fixed an issue where the bulk action Livewire component wasn't registering correctly.
- The orders table will now show the correct minutes in the timestamp.

### Changed

- When the hub password reset form is submitted, it will return a success message regardless.

### Added

- Added `top`, `bottom` slots to brands
- Added unique validation to attribute handles.

## 0.1.1

### Fixed

- Added missing method for removing variant options in product editing.

## 0.1.0

### Fixed

- Product variant will fallback on it's product's thumbnail if it has no primary image.
- Language code will now correctly be set on factory and unique.

## 0.1.0-rc.5

### Fixed

- Image manager with now use a set key when referenced by array index to keep Livewire state intact.

### Changed

- Brand requirement is now configurable via `config/lunar-hub/products.php`
- Updated publishing tag to be more consistent

## 0.1.0-rc.4

### Fixed

- Registered missing table builders as singletons.

## 0.1.0-rc.3

### Changed

- Recompiled hub assets

## 0.1.0-rc.2

> No notable changes

## 0.1.0-rc.1

Initial release.
