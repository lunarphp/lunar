# Changelog

All notable changes to `lunarphp/filament` will be documented in this file.

## Unreleased

- Added `Orders\NotifyCustomerAction` — an order header action that composes and sends a chosen customer notification (variant, optional message, recipients), delegating to `Core\Actions\Orders\NotifyCustomer`. Variants come from the order-scoped, manually-sendable entries of the core `OrderNotifications` registry; core ships a default `order-update` variant, so the action is visible out of the box and hides only when none are sendable. The same registry drives the automatic lifecycle sends, so a notification that fires on an event can also be resent from this action.
- Initial extraction from `lunarphp/admin` (formerly `lunarphp/lunar`) — see [spec 0006](../../specs/0006-filament-bridge-package.md).
