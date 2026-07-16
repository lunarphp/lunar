import { config } from '@vue/test-utils';
import { createI18n } from 'vue-i18n';

// Components call useI18n(), so every mount needs the plugin installed. No
// messages are loaded: t() falls back to the raw key, matching the app's
// behaviour before the translations endpoint has responded.
config.global.plugins.push(
    createI18n({ legacy: false, locale: 'en', fallbackLocale: 'en', messages: {}, missingWarn: false, fallbackWarn: false }),
);
