# Code Review: PR #2382 — Fix weight-based shipping rate breakpoints evaluation

**Branch:** `fix/weight-based-shipping-breakpoints-2381` -> `1.x`
**Author:** Huncsuga
**URL:** https://github.com/lunarphp/lunar/pull/2382
**Reviewed:** 2026-04-02
**HEAD:** `785cb5be14cdcfb65050855a620a38a474a89e03`

## Summary

This PR fixes a bug where weight-based shipping rates ignore or inconsistently apply pricing breakpoints due to mismatched weight units during comparison. It normalizes cart line weights to kg using the `Converter` facade, removes the `*100`/`/100` scaling on weight-based `min_quantity` values, and adds UI helper text/suffixes.

## Scoring

All issues scored below the 80-point confidence threshold. No issues were posted to the PR. The findings below are documented for reference.

---

## Findings (all below threshold)

### 1. Silent `return 0` on weight conversion failure (Score: 75)

When `weight_unit` is null or unrecognized, `Converter::from("weight.{$weight_unit}")` throws, and the `catch` block returns `0` for that cart line. This silently excludes the line's weight from the total, potentially qualifying the cart for a cheaper shipping tier.

https://github.com/lunarphp/lunar/blob/785cb5be14cdcfb65050855a620a38a474a89e03/packages/table-rate-shipping/src/Drivers/ShippingMethods/ShipBy.php#L74-L86

A fallback like `$line->purchasable->weight_unit ?: 'kg'` (consistent with the `HasDimensions` trait) would be safer than silently zeroing the weight.

---

### 2. No migration for existing `*100` stored weight breakpoints (Score: 75)

The PR removes the `*100` multiplier on save and `/100` divisor on hydrate. Any existing installation with weight-based shipping rates stored as multiplied integers (e.g., `500` meaning 5 kg, per the convention introduced in commit `f7d4648d` / PR #1961, July 2025) will have breakpoints that never match after upgrade, since `5.0 >= 500` is false.

https://github.com/lunarphp/lunar/blob/785cb5be14cdcfb65050855a620a38a474a89e03/packages/table-rate-shipping/src/Filament/Resources/ShippingZoneResource/Pages/ManageShippingRates.php#L155-L170

A database migration (or state update class per repo conventions) to rescale existing weight-mode `min_quantity` values would be needed.

---

### 3. `min_quantity == 1` treated as base price by PricingManager (Score: 75)

`PricingManager` splits prices into `base` (`min_quantity == 1`) and `priceBreaks` (`min_quantity > 1`). With the `*100` removal, a 1 kg threshold is stored as `1`, causing PricingManager to classify it as the base price rather than a breakpoint. This is an edge case for weight-based shipping that didn't exist when values were stored as `*100`.

https://github.com/lunarphp/lunar/blob/785cb5be14cdcfb65050855a620a38a474a89e03/packages/table-rate-shipping/src/Drivers/ShippingMethods/ShipBy.php#L88-L101

---

### 4. Hard-coded 'kg' suffix not translatable (Score: 75)

The helper text uses the translation system (`__('...helper_text')`), but the field suffix is hard-coded as `return 'kg'`. This is inconsistent — the helper text is translated in en/hu/ro, but the suffix is always English.

https://github.com/lunarphp/lunar/blob/785cb5be14cdcfb65050855a620a38a474a89e03/packages/table-rate-shipping/src/Filament/Resources/ShippingZoneResource/Pages/ManageShippingRates.php#L135-L143

---

### 5. Double DB query per form render (Score: 75)

Both `helperText()` and `suffix()` closures independently call `static::getShippingChargeBy()`, which queries the database for the shipping method. Combined with the pre-existing `label()` closure, this results in 3 queries for the same data per form render.

https://github.com/lunarphp/lunar/blob/785cb5be14cdcfb65050855a620a38a474a89e03/packages/table-rate-shipping/src/Filament/Resources/ShippingZoneResource/Pages/ManageShippingRates.php#L121-L143

---

### 6. Redundant `(int)(int)` cast (Score: 0 -- false positive)

The reported double cast does not exist in the actual diff. The code has a single `(int)` cast.

---

## Verdict

No issues met the 80-point confidence threshold for posting to the PR. The most notable concerns are the missing data migration for existing `*100` weight breakpoints (issue 2) and the silent `return 0` on conversion failure (issue 1), both scoring 75. These may warrant discussion with the PR author but did not meet the bar for automated review comments.
