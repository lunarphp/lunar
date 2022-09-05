# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

### [ Unreleased ]

## Fixed
- When migrating fresh, the transaction table migrations were out of order.  Changed the type when making nullable.
- Added `wire:key` to URL management
- When setting a new `default` URL whilst editing, other existing `default` values are set to `false`
- Moved the activity log on product variants outside the form element as this was conflicting.
- ProductVariants now have comments enabled.


### 2.0-beta15 - 2022-08-10

## Changed

- `savePricing` on the `GetCandy\Hub\Http\Livewire\Traits\HasPrices` trait now supports `basePrices` and `tierPrices` parameters.

## Fixed

- When variants are disabled, editing a product type will not longer display tabs for variant attributes.
- Fixed an issue where adding a comment to the activity log would error.
- Comments should now show correctly on product editing pages.
- Sortable is now attached to the browser window so it's globally available.
- Fixed an issue where incorrect attributes were showing when changing product types
- `doctrine/dbal` locked to `3.3.7` due to issue with Sqlite 

### 2.0-beta14 - 2022-08-03

## Changed

- The activity log timeline is now extendable.
- Partial card number will now only appear if last four is populated on a transaction.
- Collection searching in the hub will now return the breadcrumb, making it more obvious on the choice.
- Hub assets have been taken off CDN as much as possible in favour of being bundled with the hub assets
- Transactions UI on orders has been tweaked to include more relevant information.
- Swapped out the meta fields on the customers table in favour of showing attributes.
- Saved cart functionality has been removed.
- Complete refactor on the activity log display.

## Added

- Migrations are now publishable under the tag `getcandy-migrations`.
- Added current and purchase stock level to order lines.
- Added order notifications to order status updates.
- Added Tax Management screens.
- It's now possible for developers to extend Rich text configuration per attribute.
- Order status updates has been improved and now supports notifications
- Initial groundwork has been added to allow staff members to impersonate users on the store
- Added ability to download an order as a PDF
- Added an extra check when deleting a collection group to prevent mistakes.
- Billing details should now be visible on the orders table in the listing.
- Added a copy to clipboard button for order reference and customer reference when viewing an order.
- The full name and company of the customer has been added to the order table listing.
- Delivery instructions should be visible on orders, when they are present on the address.
- Variant slots will now appear on product editing when only one variant is present.
- Datepicker component now supports passing whole array of config.
- The stock level on order lines will show if present in the meta.
- Slots have been added to the order screen.
- Migrations are now publishable under the tag `getcandy-migrations`.
- UI update across the board.

## Fixed

- The side menu will now load correctly depending on whether expanded or collapsed without flickering.
- When adding a collection to a group it will now display the correct node tree afterwards.
- Fixed an error in Alpine caused by syntax issues for the settings menu.
- Order addresses will now display the contact phone and email address.
- The Toggle fieldtype will now correctly set to `false` when conditions are met instead of `null`.
- The `last_four` column on the transactions table now allows `null`.
- Improved the add-on manifest to correctly fetch add-ons which are installed.

### 2.0-beta13.2 - 2022-06-23

## Fixed

- Order screens will now handle null `placed_at` columns.
- Switched to using array access on dynamic scopes for Laravel 9 support
- Scoped the `FilePond` reference it's component to sort browser quirks

## Changed

- Cart/Order modifiers now require `$next` in pipeline usage.

### 2.0-beta13 - 2022-05-19

## Added

- Added functionality to restore soft deleted products from the listing view.
- Added functionality to filter products based on status and whether they are soft deleted.

## Fixed

- The Quill editor now has some added spacing between elements such as Paragraphs.
- If a fieldtype class no longer exists, the editing pages will now remove it and prevent the associated errors.
- When deleting a collection with descendants, their relationships will now be factored in. [#292](https://github.com/getcandy/getcandy/issues/292)
- When deleting a collection, their URLs will now also be removed.

## Changed

- Collection tree now uses a Livewire approach for loading, which should reduce the load on the browser. [#274](https://github.com/getcandy/getcandy/pull/274)

[View Changes](https://github.com/getcandy/admin/compare/2.0-beta12...2.0-beta13)

## 2.0-beta12 - 2022-04-08

## Fixed

- Updated exchange rate validation to allow values greater than 255.
- Fixed references to DB connection. By [@ryanmitchell](https://github.com/ryanmitchell)

[View Changes](https://github.com/getcandy/admin/compare/2.0-beta11...2.0-beta12)

## 2.0-beta11 - 2022-04-04

## Added

- Added new config option `disable_variants` to `getcandy-hub/products.php`. This is set to `false` by default so variants remain enabled.
- Added validation message for URLs when editing a product
- Added `slug` field when creating a new collection and URLs are required.
- Added ability to save searches on the orders table.
- Added a new Slot feature so developers can start extending screens within the Hub.

## Fixed

- `wire:model` now correctly references the current property when editing an attribute.
- Attribute editing validation rules will now take in to account all languages.
- The `Number` field type will now show the correct input with validation By [@lucasvmds](https://github.com/lucasvmds)

## Changed

- Customer screens have been completely overhauled.
- Order screens has been completely overhauled.
- Complete rewrite to the orders table.
- The way order statuses are defined in `config/getcandy/orders.php` has changed, see upgrade guide for detais.

[View Changes](https://github.com/getcandy/admin/compare/2.0-beta10.1...2.0-beta11)

## 2.0-beta10.1 - 2022-02-19

### Fixed

- Issue with unauthorised messages in the hub.

[View Changes](https://github.com/getcandy/admin/compare/2.0-beta9...2.0-beta10.1)

## 2.0-beta10 - 2022-02-18

### Added

- Added product association component to product editing/creation pages.
- Added collections to the product editing screen.

### Fixed

- GetCandy will now register it's bindings in the `boot` method of the service providers. By [@edcoreweb](https://github.com/edcoreweb)

[View Changes](https://github.com/getcandy/admin/compare/2.0-beta9...2.0-beta10)

## 2.0-beta9 - 2022-02-11

### Fixed

- When editing a product variant, you should now be able to select an image and save.
- Image browser on variants is now scrollable to fix an issue with it going off screen.

[View Changes](https://github.com/getcandy/admin/compare/2.0-beta8...2.0-beta9)

## 2.0-beta8 - 2022-02-01

### Fixed

- When adding images to a variant it will now set them as the primary media model.
- When fetching a variant thumbnail it will check itself before moving on to the product.
- Wrapped `placed_at` with `optional` function on dashboard to prevent error on orders without a `placed_at` value. By [@ryanmitchell](https://github.com/ryanmitchell)
- Validation on the option creator now uses the correct language code when validating. By [@green-mike](https://github.com/green-mike)

### Changed

- The login form now makes use of `wire:model.defer` and `redirect()->intended(...)` for performance and usability. By [@DanielSpravtsev](https://github.com/DanielSpravtsev)

### Added

- Added editable `tax_ref` field under pricing for products and variants.

[View Changes](https://github.com/getcandy/getcandy/compare/2.0-beta7...2.0-beta8)

## 2.0-beta7 - 2022-01-19

### Fixed

- Custom FieldType validation rules will now apply when editing a model with attributes.
- When logging in, the remember me boolean is now passed through. By ([@DanielSpravtsev](https://github.com/DanielSpravtsev)).

### Changed

- Complete rework on how attributes are edited/created in the hub
- When saving a product it will now be wrapped in a transaction to prevent data corruption on error.
- Removed dependency on `livewire-ui/modal` package since it was only used in one place. ([#47](https://github.com/getcandy/getcandy/issues/47)).
- Removed `Basket` model since it's completely redundant.
- Alpine JS has been updated to v3.

### Added

- Added functionality/UX for product variants to support attributes.
- Product types can now associate to product variant attributes.
- Attribute groups can be re ordered within their settings screens.
- Attributes within a group can now be reordered.
- Added an additional list `FieldType`.
- Tag editing screens have been added by [@briavers](https://github.com/briavers).

[View Changes](https://github.com/getcandy/getcandy/compare/2.0-beta6...2.0-beta7)

## 2.0-beta6 - 2022-01-10

### Fixed

- Use Alpine JS `x-on` instead of `@notify` to fix name collision by [@daikazu](https://github.com/daikazu) ([#41])(https://github.com/getcandy/getcandy/issues/41).

### Added

- Added account section for the current staff member update their details.
- Added password reset and remember me functionality.

[View Changes](https://github.com/getcandy/getcandy/compare/2.0-beta5...2.0-beta6)

## 2.0-beta5 - 2022-01-07

### Fixed

- Product SKU(s) will now be displayed on listing page ([#19](https://github.com/getcandy/getcandy/issues/19)).
- Product stock will now show correct value ([#19](https://github.com/getcandy/getcandy/issues/19)).
- Incoming stock on product variants will show correct values on product edit page ([#19](https://github.com/getcandy/getcandy/issues/19)).
- The name displayed on the activity log should now be accurate ([#18](https://github.com/getcandy/getcandy/issues/18)).
- Move collection button is now disabled if no target collection is selected ([#23](https://github.com/getcandy/getcandy/issues/23)).
- Tweaked the way attributes are mapped to Livewire to prevent issue with `@entangle` updating attributes listed ([#4](https://github.com/getcandy/getcandy/issues/4)).

### Changed

- Removed requirement to specify `staff` guard in `config/auth.php`. This can be safely removed.

### Added

- Initial customer listing screen.
- Initial customer editing screen.

[View Changes](https://github.com/getcandy/getcandy/compare/2.0-beta4...2.0-beta5)

## 2.0-beta4 - 2021-12-24

### Fixed

- Fixed group by statement on dashboard query by [@itcyborg](https://github.com/itcyborg).
- Fixed support for PostgreSQL on dashboard queries.

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
