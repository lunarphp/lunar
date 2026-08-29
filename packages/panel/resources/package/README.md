# @lunarphp/panel-vite-plugin

Vite plugin for compiling [Lunar](https://lunarphp.io) panel add-on bundles.

The [`lunarphp/panel`](https://github.com/lunarphp/lunar) admin panel loads add-on JavaScript at runtime as IIFE bundles that share the panel's runtime. This plugin configures that build: it compiles your add-on entry to a single IIFE, externalises `vue`, `@inertiajs/vue3`, `vue-i18n`, and [`@lunarphp/panel`](https://www.npmjs.com/package/@lunarphp/panel) to the globals the panel publishes at startup (so your bundle ships no duplicate framework code and shares the panel's Vue, Inertia, and i18n instances), and places the build manifest where Laravel's Vite integration expects it.

## Installation

```sh
npm install --save-dev @lunarphp/panel-vite-plugin @lunarphp/panel
```

## Usage

```js
// vite.config.js
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import lunarPanelPlugin from '@lunarphp/panel-vite-plugin';

export default defineConfig({
    plugins: [
        vue(),
        lunarPanelPlugin({ name: 'MyPanelAddon' }),
    ],
    build: {
        outDir: 'build',
        rollupOptions: {
            input: 'resources/js/addon.ts',
        },
    },
});
```

`name` sets the global variable your IIFE bundle is assigned to (default `LunarPanelAddon`).

## Documentation

See the [panel add-on example](https://github.com/lunarphp/lunar/tree/2.x/packages/panel-addon-example) for a full, tested walkthrough of building an add-on, and the [Lunar documentation](https://docs.lunarphp.io) for the panel itself.

## License

MIT. See [LICENSE.md](LICENSE.md).
