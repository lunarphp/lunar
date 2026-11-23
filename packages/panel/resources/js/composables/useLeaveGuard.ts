import { getCurrentInstance, getCurrentScope, onScopeDispose } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

/**
 * Prompts before staff leave a page while `isDirty()` is true. An Inertia
 * GET visit to another path asks with `window.confirm(t(messageKey))` and is
 * cancelled on decline; a hard navigation (close tab, refresh, external
 * link) gets the browser's native beforeunload prompt.
 *
 * Same-path visits (partial reloads), prefetches and non-GET visits pass
 * through: a save, a validation-error redirect back, or a separate action
 * posted from the page (a dialog's own form) never prompts. A plain page
 * form passes `() => form.isDirty`, which Inertia clears after a
 * successful submit.
 */
export function useLeaveGuard(isDirty: () => boolean, messageKey = 'common.leave_unsaved_confirm'): void {
    // Outside a component (unit tests) t() falls back to the raw key.
    const t = getCurrentInstance() ? useI18n().t : (key: string): string => key;

    const unlisten = router.on('before', (event) => {
        const { url, method, prefetch } = event.detail.visit;

        // Prefetch visits (sidebar <Link prefetch> on hover) fire the same
        // 'before' event but never leave the page, so they must not prompt.
        if (!isDirty() || prefetch || method !== 'get' || url.pathname === window.location.pathname) {
            return;
        }

        if (!window.confirm(t(messageKey))) {
            event.preventDefault();
        }
    });

    const onBeforeUnload = (event: BeforeUnloadEvent): void => {
        if (isDirty()) {
            event.preventDefault();
        }
    };

    window.addEventListener('beforeunload', onBeforeUnload);

    if (getCurrentScope()) {
        onScopeDispose(() => {
            unlisten();
            window.removeEventListener('beforeunload', onBeforeUnload);
        });
    }
}
