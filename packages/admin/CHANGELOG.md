# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## 2.0-beta7 - 2022-01-10

### Fixes

- Custom FieldType validation rules will now apply when editing a model with attributes.

### Changed

- Complete rework on how attributes are edited/created in the hub
- When saving a product it will now be wrapped in a transaction to prevent data corruption on error.
- Removed dependency on `livewire-ui/modal` package since it was only used in one place. ([#47](https://github.com/getcandy/getcandy/issues/47))
- Removed `Basket` model since it's completely redundant.
- Alpinejs has been updated to v3

### Added

- Added functionality/UX for product variants to support attributes.
- Product types can now associate to product variant attributes.
- Attribute groups can be re ordered within their settings screens.
- Attributes within a group can now be reordered.
- Added an additional List FieldType
- Tag editing screens have been added by [@briavers](https://github.com/briavers)

## 2.0-beta6 - 2022-01-10

### Fixed

- Use Alpinejs `x-on` instead of `@notify` to fix name collision by [@daikazu](https://github.com/daikazu) ([#41])(https://github.com/getcandy/getcandy/issues/41)

### Added

- Added account section for the current staff member update their details.
- Added password reset and remember me functionality.

## 2.0-beta5 - 2022-01-07

### Added
- Initial customer listing screen
- Initial customer editing screen

### Fixed
- Product SKU(s) will now be displayed on listing page ([#19](https://github.com/getcandy/getcandy/issues/19))
- Product stock will now show correct value ([#19](https://github.com/getcandy/getcandy/issues/19))
- Incoming stock on product variants will show correct values on product edit page ([#19](https://github.com/getcandy/getcandy/issues/19))
- The name displayed on the activity log should now be accurate ([#18](https://github.com/getcandy/getcandy/issues/18))
- Move collection button is now disabled if no target collection is selected ([#23](https://github.com/getcandy/getcandy/issues/23))
- Tweaked the way attributes are mapped to Livewire to prevent issue with `@entangle` updating attributes listed ([#4](https://github.com/getcandy/getcandy/issues/4))

### Changed
- Removed requirement to specify `staff` guard in `config/auth.php`. This can be safely removed.

## 2.0-beta4 - 2021-12-24
### Fixed
- Fixed group by statement on dashboard query by [@itcyborg](https://github.com/itcyborg)
- Fixed support for PostgreSQL on dashboard queries

[View Changes](https://github.com/getcandy/getcandy/compare/2.0-beta3...2.0-beta4)

## 2.0-beta3 - 2021-12-23
### Fixed
- Fixed issue where a staffs password confirmation wasn't working correctly.
- There was an issue deleting a nested collection, this has been resolved.
- Translation fixes various screens.

[View Changes](https://github.com/getcandy/getcandy/compare/2.0-beta2...2.0-beta3)

### Changed
- UX tweaks to option selector when creating/editing product variants.
- Translatable inputs will now only show translations when you have more than one language.

## 2.0-beta2 - 2021-12-23
### Fixed
- Fixed issue where compare price was set to 0 by default, causing validation issues.
- Unit quantity validation messages now show when editing a product.
- Fixed issue where dialog modals appeared behind the overlay, rendering them unusable.

[View Changes](https://github.com/getcandy/getcandy/compare/2.0-beta...2.0-beta2)

## 2.0-beta - 2021-12-22

Initial release.
