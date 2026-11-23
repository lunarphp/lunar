import { config } from '@vue/test-utils';
import { createI18n } from 'vue-i18n';

// Components call useI18n(), so every mount needs the plugin installed. No
// messages are loaded: t() falls back to the raw key, matching the app's
// behaviour before the translations endpoint has responded.
config.global.plugins.push(
    createI18n({ legacy: false, locale: 'en', fallbackLocale: 'en', messages: {}, missingWarn: false, fallbackWarn: false }),
);

// Reka tooltips need the layout's TooltipProvider, which a component mount
// does not have. Render through and surface the text, so tests can read it
// from data-tooltip. Section's Applies immediately marker uses one.
config.global.stubs.Tooltip = { props: ['text'], template: '<span :data-tooltip="text"><slot /></span>' };
