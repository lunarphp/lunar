# Lunar 1.x documentation

The source of the 1.x pages on [docs.lunarphp.com](https://docs.lunarphp.com/1.x). Write docs in the same pull request as the code they describe.

## Publishing

Docs publish with releases. When a 1.x release is published, `lunarphp/docs` copies this folder from that release's tag into `1.x/` and rebuilds the site. A change merged here goes live at the next 1.x release; until then the site shows the docs from the last one. Don't edit `1.x/` in `lunarphp/docs`, the next sync overwrites it.

The `1.4` maintenance branch has no docs. v1 docs changes go here, on `1.x`.

## Writing pages

- Add every new page to the navigation in `docs.json`. Only its `navigation` is published; the theme, logo, redirects and the Support and Flight Plan tabs come from `lunarphp/docs`.
- Write page paths and links without a version prefix: `reference/carts` in `docs.json`, `[Carts](/reference/carts)` in a page. The prefix is added when the docs are published.
- Link to another version, or to Support, Flight Plan or v0 pages, with the full URL: `https://docs.lunarphp.com/2.x/...`, `https://docs.lunarphp.com/support/community`. Publishing prefixes every root-relative link with `/1.x`, so only this version's pages are root-relative.
- Put images in `images/` and reference them as `/images/...`.

## Writing style

- Check everything against the code on this branch. Never document a class, method, config key or Artisan command without verifying it here, and test example code. Core models live under `Lunar\Models\...`.
- Give code references their full namespace, with a `use` statement or written out in full:

  ```php
  use Lunar\Models\Cart;

  $unmergedCarts = Cart::unmerged()->get();
  ```

- Write in the third person. Never use "we", "our" or "us"; refer to Lunar, the package or the feature.
- Write in American English that a global audience can follow. Avoid idioms: many readers do not have English as their first language.
- Use em dashes sparingly. Prefer commas, colons or parentheses.
- Follow the page heading with one sentence summarizing the topic.

### Models

Cover a model's fields, relationships, scopes and methods.

Show fields as a table with the columns Field, Type and Description:

- List fields in the same order as the database schema.
- Show the Laravel column type, for example `longText`.
- Describe `id` as "primary key".
- Mark nullable fields with `nullable` as inline code at the end of the Type column, for example `` `foreignId` `nullable` ``. Leave non-nullable fields unmarked.
- Include the standard timestamps (`created_at`, `updated_at`, `deleted_at`) without a description.
- Leave obvious fields, such as `first_name`, without a description.

Show relationships as a table with the columns Relationship, Type, Related Model and Description, and scopes as a table with the columns Scope and Description.

## Previewing

```bash
npm i -g mint
cd docs
mint dev            # http://localhost:3000
mint validate
mint broken-links
```

The preview uses this folder's `docs.json`, so it shows 1.x on its own, without the version switcher or the shared tabs. The `Docs` workflow runs `mint validate`, `mint broken-links` and a check for root-relative links to other versions and shared pages on every pull request that changes `docs/`.
