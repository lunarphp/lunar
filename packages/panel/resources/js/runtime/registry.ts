import type { Component } from 'vue';

/**
 * `window.LunarPanel` — the registry add-on IIFE bundles talk to. Add-ons may load before
 * or after this runtime boots (script order is not guaranteed), so registration methods
 * are safe to call at any time and `booting()` queues callbacks until boot completes.
 */
export class LunarPanelRuntime {
    private booted = false;
    private pendingBootCallbacks: Array<() => void> = [];
    private warnedMissingComponents = new Set<string>();

    private pages: Record<string, Component> = {};
    private components: Record<string, Component> = {};
    private layouts: Record<string, Component> = {};
    private translations: Record<string, Record<string, Record<string, string>>> = {};

    booting(cb: () => void): void {
        if (this.booted) {
            cb();

            return;
        }

        this.pendingBootCallbacks.push(cb);
    }

    /** Called by app.ts once the Inertia app has mounted. */
    markBooted(): void {
        this.booted = true;

        const callbacks = this.pendingBootCallbacks;
        this.pendingBootCallbacks = [];

        callbacks.forEach((cb) => cb());
    }

    registerPages(pages: Record<string, Component>): void {
        Object.assign(this.pages, pages);
    }

    getPage(name: string): Component | undefined {
        return this.pages[name];
    }

    registerComponents(namespace: string, components: Record<string, Component>): void {
        Object.entries(components).forEach(([key, component]) => {
            this.components[`${namespace}::${key}`] = component;
        });
    }

    resolveExtensionComponent(name: string): Component | undefined {
        const component = this.components[name];

        if (!component && !this.warnedMissingComponents.has(name)) {
            this.warnedMissingComponents.add(name);
            console.warn(`[LunarPanel] Extension component "${name}" is not registered.`);
        }

        return component;
    }

    registerLayout(name: string, layout: Component): void {
        this.layouts[name] = layout;
    }

    getLayout(name: string): Component | undefined {
        return this.layouts[name];
    }

    registerTranslations(locale: string, namespace: string, messages: Record<string, string>): void {
        this.translations[locale] ??= {};
        this.translations[locale][namespace] = {
            ...this.translations[locale][namespace],
            ...messages,
        };
    }

    getTranslations(locale: string, namespace: string): Record<string, string> | undefined {
        return this.translations[locale]?.[namespace];
    }
}
