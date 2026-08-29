import { ref } from 'vue';

/**
 * Module-scoped open state, so the sidebar button, the Cmd+K binding, and any
 * add-on surface can open the palette without threading props through the
 * layout.
 */
const open = ref(false);

export function useCommandPalette() {
    function openPalette(): void {
        open.value = true;
    }

    function closePalette(): void {
        open.value = false;
    }

    function togglePalette(): void {
        open.value = !open.value;
    }

    return { open, openPalette, closePalette, togglePalette };
}
