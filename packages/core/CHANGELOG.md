# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## 0.2.1

### Fixed

- The `CalculateTax` pipeline should now correctly set the tax breakdown across the cart and lines.

## 0.2-RC3

### Fixed

- Fixed serialisation issue on `tax_breakdown` when creating an order.
- Cart cached properties should now refresh correctly when calculating.

### Changed

- The GetUnitPrice action will now use the cart user if present rather than the authenticated user.
- Discounts will now take other discounts into account before they apply their own logic.
- Cart meta is now cast to an `array` when adding a purchasable.

### Added

- Added discount breakdowns to cart and order models in https://github.com/lunarphp/lunar/pull/884
- Added max uses by user on discounts.

## 0.2-RC2

### Fixed

- `brand_id` is now fillable on the `Product` model.
- Brand URL should now generate automatically when created.
- The Discount (AmountOff) type should reference the `purchasable` relation correctly.

### Changed

- Renamed `Discount` discount type to `AmountOff`
- Fixed price discounts will now spread the amount across all eligible cart lines.
- `cartDiscountAmount` property has been removed.

### Added

- Added `subTotalDiscounted` property to CartLine which shows the sub total with the discount.

## 0.2-RC1

### Fixed

- Fix/get_class of extended polymoprhic model by @wychoong in https://github.com/lunarphp/lunar/pull/807
- Use boolean mode for scout driver by @afbora in https://github.com/lunarphp/lunar/pull/808
- Hotfix [0.2] - Fix language deletion by @alecritson in https://github.com/lunarphp/lunar/pull/811
- fixed the minSpend condition on Discount Feature by @0xenm21 in https://github.com/lunarphp/lunar/pull/823
- Add correct params to TransactionObserver by @ryanmitchell in https://github.com/lunarphp/lunar/pull/815
- Fix issue when discount relation dates are null by @ryanmitchell in https://github.com/lunarphp/lunar/pull/797
- Hotfix [0.2] - Fix duplicate language code on tests by @alecritson in https://github.com/lunarphp/lunar/pull/802
- Hotfix [0.2] - Add test to save cart coupon by @alecritson in https://github.com/lunarphp/lunar/pull/804
- [0.2] Hotfix - Delete CartLineManager.php by @alecritson in https://github.com/lunarphp/lunar/pull/777
- [0.2] Hotfix - Add active scope to user/cart association by @alecritson in https://github.com/lunarphp/lunar/pull/754

### Changed

- [0.2] Cart refactor by @alecritson in https://github.com/lunarphp/lunar/pull/676

### Added

- Increment discount uses as part of order creation by @ryanmitchell in https://github.com/lunarphp/lunar/pull/814
- Add products to discount limitations by @ryanmitchell in https://github.com/lunarphp/lunar/pull/813
- [0.2] Add clearOptions() method to ShippingManifest by @webcraft in https://github.com/lunarphp/lunar/pull/775
- [0.2] Allow extending of validation rules by @wychoong in https://github.com/lunarphp/lunar/pull/443
- [0.2] Discounts by @alecritson in https://github.com/lunarphp/lunar/pull/324
- [0.2] Feat - Ability to mark orders as new customer by @alecritson in https://github.com/lunarphp/lunar/pull/769

**Full Changelog**: https://github.com/lunarphp/lunar/compare/0.1.4...0.2-rc1

## 0.1.5

### Fixed

- Cart manager will now check for active order before associating and merging carts.
- Price update will now correctly save the compare price.

## 0.1.4

### Fixed

- Prices are now stored as `bigInt` to avoid out of range errors for certain currencies.

## 0.1.3

### Fixed

- Transaction activity log should now store the correct properties.

### Added

- Added user emails to customer index on search.

## 0.1.2

### Added

- Added shorthand `attr` which can be used instead of `translateAttribute`

## 0.1.1

> No notable changes

## 0.1.0

> No notable changes

## 0.1.0-rc.5

### Changed

- Updated publishing tag to be more consistent

## 0.1.0-rc.4

### Fixed

- Lunar migration command will now add brands into the correct table.
- Added additional check for language existence when running the brand update state class.
- Publishing commands now use a consistent syntax.

## 0.1.0-rc.3

> No notable changes

## 0.1.0-rc.2

> No notable changes

## 0.1.0-rc.1

Initial release.
