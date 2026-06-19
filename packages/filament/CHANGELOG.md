# Changelog

All notable changes to `lunarphp/filament` will be documented in this file.

## Unreleased

- Added `Orders\NotifyCustomerAction` — an order header action that composes and sends a chosen customer notification (variant, optional message, recipients), delegating to `Core\Actions\Orders\NotifyCustomer`. Core ships a default `order-update` variant, so the action is visible out of the box; it hides only if the `CustomerNotifications` catalogue is emptied.
- Initial extraction from `lunarphp/admin` (formerly `lunarphp/lunar`) — see [spec 0006](../../specs/0006-filament-bridge-package.md).
