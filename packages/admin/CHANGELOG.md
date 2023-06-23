# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## [Unreleased]

### Fixed

- `values` are now detached from the product variant when it's the only one left.

### Changed

- Improved `wire:key` on product associations when editing a product to avoid duplicate keys causing DOM diffing issues.

## 0.3.0

> Latest updates from `0.2` have been brought in.

### Fixed

- The sales dashboard graph now correctly shows figures.
- When going to a new page on table listings, the page will now scroll to the top.
- The iframe height on email previews has been increased to avoid cut off.
- Refactor row selection on tables to avoid extra roundtrips to Livewire.
- AttributeGroup saving will now pass the id if being edited to prevent false validation exception being thrown.

### Changed

- Dashboard stats are now responsible for their own date ranges where applicable.
- The tags panel on the order detail screen has been moved below the address details.
- Brands will now list alphabetically by default.
- MySQL 5.7 is no longer supported, MySQL 8.0+ is required.

### Added

- Brands are now searchable and can have saved searches.

## 0.3.0-beta.3

> Maintenance update to bring changes from 0.2.5 in.

## 0.3.0-beta.2

> Maintenance update to bring changes from 0.2.4 in.

## 0.3.0-alpha.4

### Changed

- Updated Lunar branding

## 0.3.0-alpha.3

> No notable changes

## 0.3.0-alpha.2

### Fixed

- Fixed a bug where existing product associations were not passed correctly on discount editing
- Fixed a bug with popovers closing on mouse click

## 0.3-alpha

### Changed

- Laravel 8 support removed.
- Laravel 10 support added.
- Changed Auth guard to use Laravel's default driver.
- Updated `db_date` function to return just the formmatted string.

## 0.2.7

### Fixed

- Added missing notification translation.
- Variant stock level will now be validated correctly to prevent DB exception on save.
- Variant image uploading will now check for S3 usage when uploading, the same as product saving.
- When reordering items, the `$index` now checks for an integer instead of purely existence to prevent failure
  when `$index` is `0`.
- Improved validation on translatable attributes to prevent exception when entering multiple languages.

## 0.2.6

### Fixed

- When creating an option, `wire:model` should now correctly reference `newProductOption` when editing the name in a
  different locale.

## 0.2.5

### Fixed

- When saving a discount for the first time using BuyXGetY, the save method will now ensure the id is set.
- Datetime picker will now clear date properly and reset the flatpickr instance.
- Discounts end date will now validate against that start date properly when saving.
- Added missing listeners to components which handle attributes.
- Non existent (deleted) products will be filtered from associations when editing a product.

### Changed

- Datetime picker now uses `.defer`

### Added

- Added `DiscountTypes` facade to enable discount extending in the hub.

## 0.2.4

### Changed

- Alpinejs CDN now points to `jsdelivr` as per the recommendation.

## 0.2.3

### Fixed

- Non tiered pricing will now use the `bcmul` when saving.

## 0.2.2

### Fixed

- Fixed wrong `$customer` variable on brand slot.

## 0.2-RC3

### Fixed

- Fixed an issue that prevented bulk actions from remembering the selected rows.

### Added

- Added the ability to specify the maximum length of textarea fields in the admin hub

## 0.2-RC2

### Fixed

- The delete form for a discount should now show.
- Validation across multiple languages has been fixed when editing attribute groups.
- Validation when creating a collection should now take languages into account
- The `HasImages` trait now uses Livewire to determine whether S3 is used.
- Order lines now has more checks for whether a purchasable exists first.
- When creating a collection, the reloaded tree should now only show collections in that group
- Ensure correct `connection` is passed when using the `DB` facade.

### Changed

- `isActive` check on `MenuLink` now handles an array of slug handles.

### Added

## 0.2-RC1

### Fixed

- [0.2] Hotfix - Upload Image by @Aslam97 in https://github.com/lunarphp/lunar/pull/810
- Hotfix [0.2] - Fix duplicate language code on tests by @alecritson in https://github.com/lunarphp/lunar/pull/802
- Hotfix [0.2] - UI Tweaks by @alecritson in https://github.com/lunarphp/lunar/pull/805
- fix blade directive issue causing error on products index page in hub by @kylekanderson
  in https://github.com/lunarphp/lunar/pull/801
- [0.2] Hotfix - Fix save form layout and clean up by @alecritson in https://github.com/lunarphp/lunar/pull/795
- fix images key by @wychoong in https://github.com/lunarphp/lunar/pull/789
- Hotfix - Fix discount saving by @alecritson in https://github.com/lunarphp/lunar/pull/793
- [0.2] Hotfix - Check for variants existence when saving images. by @alecritson
  in https://github.com/lunarphp/lunar/pull/778
- [0.2] Hotfix - Fix product/collection syncing by @alecritson in https://github.com/lunarphp/lunar/pull/781
- [0.2] Feat - Delay loading Collection products when large amounts by @alecritson
  in https://github.com/lunarphp/lunar/pull/770
- [0.2] Feat - Add fallback to images by @charlielangridge in https://github.com/lunarphp/lunar/pull/682

### Changed

- ux updates by @wychoong in https://github.com/lunarphp/lunar/pull/812
- Change of UI on discount limitations to use popover by @ryanmitchell in https://github.com/lunarphp/lunar/pull/796
- Improve UX for image manager by @rubenvanerk in https://github.com/lunarphp/lunar/pull/806
- [0.2] Feat - Menu layout refactor by @alecritson in https://github.com/lunarphp/lunar/pull/794
- [0.2] Nested menu by @markmead in https://github.com/lunarphp/lunar/pull/680

### Added

- [0.2] Preview/View URLs for Products by @alecritson in https://github.com/lunarphp/lunar/pull/772
- [0.2] Image editor by @wychoong in https://github.com/lunarphp/lunar/pull/505
- Feat [0.2] - Enable validation extending on product variant by @alecritson
  in https://github.com/lunarphp/lunar/pull/824
- Add products to discount limitations by @ryanmitchell in https://github.com/lunarphp/lunar/pull/813
- [0.2] File upload field type by @alecritson in https://github.com/lunarphp/lunar/pull/452
- [0.2] Manage customer groups by @adam-code-labx in https://github.com/lunarphp/lunar/pull/496
- [0.2] Settings - Manage Product Options by @adam-code-labx in https://github.com/lunarphp/lunar/pull/419
- [0.2] Allow extending of validation rules by @wychoong in https://github.com/lunarphp/lunar/pull/443
- [0.2] Discounts by @alecritson in https://github.com/lunarphp/lunar/pull/324
- [0.2] Add tags to orders by @alecritson in https://github.com/lunarphp/lunar/pull/433

**Full Changelog**: https://github.com/lunarphp/lunar/compare/0.1.4...0.2-rc1

## 0.1.5

### Fixed

- Fixed incorrect translations when restoring staff members.
- Brand name validation has been improved.
- Fixed an issue where users would be unable to remove newly added URLs before saving.
- Fixed an issue where users were unable to remove product associations.

### Added

- Added a `<x-hub::thumbnail>` component to keep image appearance consistent.
- Various UI improvements

### Changed

- Lunar version is no longer hard coded to `2.0-beta` if no suitable version is found.
- Improved layout for error messages when editing customer group pricing.

## 0.1.4

### Fixed

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
