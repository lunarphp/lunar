import { beforeEach, describe, expect, it, vi } from 'vitest';
import { defineComponent } from 'vue';
import { LunarPanelRuntime } from './registry';

describe('LunarPanelRuntime', () => {
    let runtime: LunarPanelRuntime;

    beforeEach(() => {
        runtime = new LunarPanelRuntime();
    });

    describe('booting', () => {
        it('fires the callback immediately when already booted', () => {
            runtime.markBooted();
            const cb = vi.fn();

            runtime.booting(cb);

            expect(cb).toHaveBeenCalledTimes(1);
        });

        it('queues callbacks and drains them in FIFO order on markBooted', () => {
            const order: number[] = [];
            runtime.booting(() => order.push(1));
            runtime.booting(() => order.push(2));
            runtime.booting(() => order.push(3));

            expect(order).toEqual([]);

            runtime.markBooted();

            expect(order).toEqual([1, 2, 3]);
        });
    });

    describe('pages', () => {
        it('round-trips registerPages/getPage', () => {
            const page = defineComponent({});
            runtime.registerPages({ Dashboard: page });

            expect(runtime.getPage('Dashboard')).toBe(page);
        });
    });

    describe('components', () => {
        it('resolves a registered component', () => {
            const component = defineComponent({});
            runtime.registerComponents('acme', { Widget: component });

            expect(runtime.resolveExtensionComponent('acme::Widget')).toBe(component);
        });

        it('returns undefined and warns exactly once for a missing name, even across repeated calls', () => {
            const warn = vi.spyOn(console, 'warn').mockImplementation(() => {});

            expect(runtime.resolveExtensionComponent('acme::Missing')).toBeUndefined();
            expect(runtime.resolveExtensionComponent('acme::Missing')).toBeUndefined();
            expect(runtime.resolveExtensionComponent('acme::Missing')).toBeUndefined();

            expect(warn).toHaveBeenCalledTimes(1);

            warn.mockRestore();
        });
    });

    describe('translations', () => {
        it('merges repeated registerTranslations calls for the same locale/namespace instead of clobbering', () => {
            runtime.registerTranslations('en', 'core', { hello: 'Hello' });
            runtime.registerTranslations('en', 'core', { bye: 'Bye' });

            expect(runtime.getTranslations('en', 'core')).toEqual({ hello: 'Hello', bye: 'Bye' });
        });

        it('notifies a listener attached after registration has already happened', () => {
            runtime.registerTranslations('en', 'core', { hello: 'Hello' });

            const listener = vi.fn();
            runtime.onTranslationsRegistered(listener);

            expect(listener).toHaveBeenCalledTimes(1);
            expect(listener).toHaveBeenCalledWith('en', 'core', { hello: 'Hello' });
        });

        it('notifies a listener attached before registration happens', () => {
            const listener = vi.fn();
            runtime.onTranslationsRegistered(listener);

            runtime.registerTranslations('en', 'core', { hello: 'Hello' });

            expect(listener).toHaveBeenCalledTimes(1);
            expect(listener).toHaveBeenCalledWith('en', 'core', { hello: 'Hello' });
        });
    });
});
