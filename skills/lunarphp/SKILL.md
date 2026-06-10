---
name: lunarphp
description: "Develops e-commerce features with LunarPHP. Activates when working with products, variants, cart, checkout, orders, discounts, payments, pricing, search, collections, attributes, or any LunarPHP model/facade/config. Also activates when the user mentions Lunar, LunarPHP, product catalog, e-commerce cart, order management, or storefront checkout in a Laravel project. Make sure to use this skill whenever the user works with e-commerce functionality, even if they don't explicitly mention Lunar."
license: MIT
metadata:
  author: lunarphp
---

# LunarPHP E-Commerce Development

## Documentation

LunarPHP docs are at `https://docs.lunarphp.com/1.x/`. The LLMS index is at `https://docs.lunarphp.com/llms.txt`.

Use `search-docs` for pattern-specific queries — Lunar docs are indexed by the search tool.

## When to Apply

Activate this skill when:

- Creating or modifying products, variants, product options, brands, or collections
- Building cart functionality (add/update/remove items, coupons, totals)
- Implementing checkout flow (addresses, shipping, payment, order creation)
- Managing orders, order lines, addresses, transactions, and refunds
- Setting up discounts (AmountOff, BuyXGetY, custom types)
- Integrating payment drivers (Stripe, PayPal, offline, custom)
- Configuring pricing pipelines, formatting, and customer group pricing
- Setting up search indexing with Scout for Lunar models
- Working with attributes, attribute groups, and custom field types
- Extending Lunar models with dynamic relationships or model replacement
- Configuring channels, currencies, languages, tax zones, and customer groups
- Building storefront features: catalog menu, PLP, PDP, cart, checkout

## Key Concepts

### Core Models & Namespaces

All Lunar models live under the `Lunar\Models` namespace:

| Model | Purpose |
|-------|---------|
| `Product` | Core product with variants, options, attributes |
| `ProductVariant` | Purchasable variant with pricing, stock, dimensions |
| `ProductOption` | Option definitions (e.g. "Color", "Size") |
| `ProductOptionValue` | Individual values for options |
| `ProductType` | Determines which attributes are available per product |
| `Brand` | Optional brand association |
| `Collection` | Hierarchical product groups via nested sets |
| `CollectionGroup` | Top-level container for collections |
| `Cart` | Shopping cart with lines, addresses, calculated totals |
| `CartLine` | Line item in a cart, linked to a purchasable |
| `CartAddress` | Shipping/billing address on a cart |
| `Order` | Completed or draft purchase from a cart |
| `OrderLine` | Line item in an order |
| `OrderAddress` | Address on an order |
| `Transaction` | Payment transaction (capture, intent, refund) |
| `Discount` | Discount/coupon with type-specific data |
| `Price` | Pricing per variant, currency, customer group, quantity break |
| `Currency` | Currency configuration with exchange rates |
| `Channel` | Sales outlet (webstore, mobile, wholesale) |
| `CustomerGroup` | Customer segment for pricing/visibility |
| `TaxClass` | Tax categorization for products |
| `TaxZone` | Geographic region for tax calculation |
| `TaxRate` | Tax rate within a zone |
| `Attribute` | Custom data field definition |
| `AttributeGroup` | Logical grouping of attributes |
| `Country` | Country reference data |
| `Url` | SEO-friendly URL slugs (polymorphic) |

### Key Facades

| Facade | Purpose |
|--------|---------|
| `Lunar\Facades\CartSession` | Session-based cart management |
| `Lunar\Facades\Pricing` | Fetch correct price for variant/criteria |
| `Lunar\Facades\Payments` | Payment driver management |
| `Lunar\Facades\ShippingManifest` | Shipping option retrieval |
| `Lunar\Facades\Discounts` | Discount application and coupon validation |
| `Lunar\Facades\PricingManager` | Pricing pipeline management |

### Config Files

All under `config/lunar/`. Key files:

- `cart.php` — Cart behavior, pipelines, validators, fingerprint generator, pruning
- `cart_session.php` — Session key, auto-create, multiple orders per cart
- `payments.php` — Payment type definitions and driver mapping
- `pricing.php` — Pricing pipelines and formatter
- `orders.php` — Order pipelines, reference generator, statuses
- `search.php` — Searchable models, engine map, indexers
- `taxes.php` — Tax driver selection
- `database.php` — Table prefix, user ID field type
- `discounts.php` — Coupon validator class
- `shipping.php` — Shipping configuration and units of measure
- `media.php` — Media/image handling

The database uses a `lunar_` table prefix (configurable in `config/lunar/database.php`). All prices are stored as integers (smallest currency unit, e.g. cents).

## Storefront Guides

These official guides provide step-by-step walkthroughs for common storefront features.
Use them to avoid duplicating effort between the skill and the docs:

| Guide | Description |
|-------|-------------|
| [Catalog Menu](https://docs.lunarphp.com/1.x/guides/catalog-menu.md) | Build a catalog navigation menu with collection groups, nested collections, active state tracking, and caching |
| [Product Listing Page](https://docs.lunarphp.com/1.x/guides/product-listing-page.md) | Collection resolution, product querying, filtering, sorting, pagination, and display |
| [Product Display Page](https://docs.lunarphp.com/1.x/guides/product-display-page.md) | Product resolution, variant selection, pricing, images, and add-to-cart |
| [Cart](https://docs.lunarphp.com/1.x/guides/cart.md) | Cart page with line items, quantity updates, coupon codes, and order totals |
| [Checkout](https://docs.lunarphp.com/1.x/guides/checkout.md) | Address collection, shipping options, order creation, and payment processing |
| [Customer Authentication](https://docs.lunarphp.com/1.x/guides/customer-authentication.md) | Link Laravel users to Lunar customers, manage sessions, and cart association |
| [Customer Addresses](https://docs.lunarphp.com/1.x/guides/customer-addresses.md) | Address listing, creation, editing, deletion, and default address selection |
| [Order History](https://docs.lunarphp.com/1.x/guides/order-history.md) | Customer-facing order listing, pagination, and order detail views |
| [Payment Integration](https://docs.lunarphp.com/1.x/guides/payment-integration.md) | Stripe payment intents, frontend elements, webhooks, and the complete payment lifecycle |
| [Search & Product Discovery](https://docs.lunarphp.com/1.x/guides/search.md) | Search setup, querying, faceted filtering, and URL-based product resolution |

## Quick Reference

Load the relevant reference file for detailed code examples:

| Topic | Reference |
|-------|-----------|
| Post-install config, artisan commands, starter kits | [Installation](./references/installation.md) |
| Products, variants, options, brands, pricing, collections | [Products & Catalog](./references/products-catalog.md) |
| Cart, CartSession, checkout, orders, transactions | [Cart, Checkout & Orders](./references/cart-checkout.md) |
| Payment drivers, discounts, custom discount types | [Payments & Discounts](./references/payments-discounts.md) |
| Search indexing, attributes, field types | [Search & Attributes](./references/search-attributes.md) |
| Channels, customer groups, currencies, tax | [System Settings](./references/system-settings.md) |
| Model replacement, dynamic relationships, pipelines, admin panel | [Extending Lunar](./references/extending.md) |
| Common mistakes and gotchas | [Common Pitfalls](./references/pitfalls.md) |

## Common Pitfalls

- **`name` attribute required**: Internally expects `name` in product/collection attribute data. Missing it causes admin panel errors.
- **Prices stored as integers**: Always store prices in smallest currency unit (cents/pence). A $19.99 price is `1999`.
- **Cart prices are dynamic**: Not stored in DB until order is created. Always call `calculate()` or `recalculate()`.
- **`MissingCurrencyPriceException`**: Thrown when fetching price for a currency with no pricing defined for the variant.
- **Cart validation exceptions**: All throw `CartException`. Catch and display `$e->errors()` for detailed messages.
- **Single order per cart by default**: Pass `allowMultipleOrders: true` to `createOrder()` for split shipments.
- **Fingerprint mismatch**: Use `checkFingerprint()` before payment to detect cart changes between checkout steps.
- **Model replacement conflicts**: Add-ons must never replace core models. Use dynamic relationships instead.
- **Scout `soft_delete`**: Must be `true` in `config/scout.php` or soft-deleted models appear in search results.
- **Root collection required**: At least one root collection must exist before child collections can be created.

> Full list with details in [Common Pitfalls](./references/pitfalls.md).
