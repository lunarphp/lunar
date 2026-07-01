# Changelog

All notable changes to `lunarphp/filament` will be documented in this file.

## Unreleased

- Added `Schemas\Region\RegionForm` and `Tables\Region\RegionTable` for the new `Region` model, plus a `region` lang file across all 16 locales — see [spec 0039](../../specs/0039-region.md).
- Removed `Schemas\TaxZone\TaxZoneForm::getPriceDisplayComponent()` and the `taxzone.form.price_display` lang keys. The per-zone price-display preference is superseded by the region's display preference, and the underlying `tax_zones.price_display` column is dropped in core — see [spec 0039](../../specs/0039-region.md).
- Added `Orders\NotifyCustomerAction` — an order header action that composes and sends a chosen customer notification (variant, optional message, recipients), delegating to `Core\Actions\Orders\NotifyCustomer`. Variants come from the order-scoped, manually-sendable entries of the core `OrderNotifications` registry; core ships a default `order-update` variant, so the action is visible out of the box and hides only when none are sendable. The same registry drives the automatic lifecycle sends, so a notification that fires on an event can also be resent from this action.
- Initial extraction from `lunarphp/admin` (formerly `lunarphp/lunar`) — see [spec 0006](../../specs/0006-filament-bridge-package.md).
