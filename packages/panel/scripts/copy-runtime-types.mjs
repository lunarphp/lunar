// Ship the window.LunarPanel runtime declarations with @lunarphp/panel:
// copy the hand-written d.ts into the generated dist and reference it from
// ui.d.ts so importing the package types the global. Run after vue-tsc
// (vue-tsc treats an existing .d.ts as input only and never re-emits it).
import { copyFileSync, readFileSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = dirname(fileURLToPath(import.meta.url));
const dist = join(root, '../resources/panel-package/dist');

copyFileSync(join(root, '../resources/js/runtime/lunar-panel.d.ts'), join(dist, 'runtime.d.ts'));

const ui = join(dist, 'ui.d.ts');
const reference = '/// <reference path="./runtime.d.ts" />\n';
const contents = readFileSync(ui, 'utf8');

if (!contents.startsWith(reference)) {
    writeFileSync(ui, reference + contents);
}
