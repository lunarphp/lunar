# @lunarphp/panel

Layout and page-chrome components for building [Lunar](https://lunarphp.io) panel add-on pages.

The [`lunarphp/panel`](https://github.com/lunarphp/lunar) Composer package is Lunar's first-party admin panel (Inertia.js + Vue 3). Add-on packages extend it at runtime — pages, navigation, table columns, actions, screen sections — without the host app recompiling any JavaScript. This npm package is the component surface those add-on pages build with: `PageHeader`, `SettingsShell`, `DataTable`, form inputs, overlays, charts, and the rest of the exported set.

## Installation

```sh
npm install --save-dev @lunarphp/panel @lunarphp/panel-vite-plugin
```

## Usage

Import components as usual in your add-on's Vue pages:

```vue
<script setup lang="ts">
import { PageHeader, DataTable, Button } from '@lunarphp/panel';
</script>
```

At build time, [`@lunarphp/panel-vite-plugin`](https://www.npmjs.com/package/@lunarphp/panel-vite-plugin) externalises the `@lunarphp/panel` import to the panel's runtime global, so your bundle shares the panel's Vue instance and component implementations — this package contributes type declarations and editor support during development, not duplicated runtime code.

The panel applies its `PanelLayout` shell to add-on pages automatically; a page supplies only its own `PageHeader` (or `SettingsShell`) and content.

## Documentation

See the [panel add-on example](https://github.com/lunarphp/lunar/tree/2.x/packages/panel-addon-example) for a full, tested walkthrough of building an add-on, and the [Lunar documentation](https://docs.lunarphp.io) for the panel itself.

## License

MIT. See [LICENSE.md](LICENSE.md).
