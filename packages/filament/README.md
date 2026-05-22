# Lunar Filament Bridge

Filament v5 components, widgets, schemas, and tables — the reusable building blocks behind the Lunar admin panel, packaged so you can drop them into any Filament panel.

## Install

```bash
composer require lunarphp/core lunarphp/filament
```

## When to use this

- You want a Filament panel tailored to your own brand and ship some of Lunar's primitives (product form, order table, dashboard widgets) into it.
- You want to compose Lunar's commerce-shaped Filament classes with your own resources.

If instead you want Lunar's turnkey admin panel, install [`lunarphp/admin`](../admin) — it depends on this package and registers a complete Filament panel for you.

## Three ways to customise

| Approach | When to reach for it | Upgrade impact |
| --- | --- | --- |
| **Extension hooks** — `LunarFilament::extensions([…])` | You want to add/modify components on an existing schema without owning the file | Additive — bridge improvements still reach you on minor releases |
| **Subclass and rebind** — bind your subclass in the container | You want to fully replace a schema/table class without copying it | Full replacement — bridge improvements still reach the parent class methods you don't override |
| **Publish stubs** — `vendor:publish --tag=lunar-filament.schemas` | You want full ownership of one or more files in your app namespace | One-way door — bridge improvements no longer reach the published file; re-merge by hand |

## Standalone usage

```php
use Lunar\Filament\Schemas\Brand\BrandForm;

class MyBrandResource extends \Filament\Resources\Resource
{
    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return BrandForm::configure($schema);
    }
}
```

## Versioning

The bridge follows Filament's release cadence. Filament major bumps (v5 → v6) drive a bridge major; the Lunar admin shell tightens its constraint when ready.

For v2 the package is developed inside the [Lunar monorepo](https://github.com/lunarphp/lunar). It extracts into its own repository at the v2.0.0 stable cut.
