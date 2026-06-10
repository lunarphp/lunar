# Common Pitfalls

- **`name` attribute required**: Internally expects `name` in product/collection attribute data. Missing it causes admin panel errors.
- **Prices stored as integers**: Always store prices in smallest currency unit (cents/pence). A $19.99 price is `1999`.
- **Cart prices are dynamic**: Not stored in DB until order is created. Always call `calculate()` or `recalculate()`.
- **`MissingCurrencyPriceException`**: Thrown when fetching price for a currency with no pricing defined for the variant.
- **Cart validation exceptions**: All throw `CartException`. Catch and display `$e->errors()` for detailed messages.
- **Single order per cart by default**: Pass `allowMultipleOrders: true` to `createOrder()` for split shipments.
- **`canCreateOrder()` before `createOrder()`**: Check readiness without throwing exceptions.
- **Fingerprint mismatch**: Use `checkFingerprint()` before payment to detect cart changes between checkout steps.
- **Model replacement conflicts**: Add-ons must never replace core models. Use dynamic relationships instead.
- **Scout `soft_delete`**: Must be `true` in `config/scout.php` or soft-deleted models appear in search results.
- **Engine map fallback**: Models not in `engine_map` use the default `SCOUT_DRIVER`. Configure per model to control indexing costs.
- **`scheduleCustomerGroup` / `scheduleChannel` with no dates**: Enables immediately. Pass `null` for `ends_at` to never expire.
- **Root collection required**: At least one root collection must exist before child collections can be created.
- **Tax zone clearance on address set**: Setting shipping address clears explicit `tax_zone_id`. Pass `clearTaxZone: false` to keep it.
- **BuyXGetY collection conditions**: Match against entire collections (added in 1.5) instead of listing individual products.
- **`resetDiscounts()` after coupon application**: Discounts are cached per request. Call this after applying a coupon to refresh.
