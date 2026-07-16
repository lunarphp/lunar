import { reactive, watch } from 'vue';

const STORAGE_KEY = 'lunar-panel-nav-collapsed';

function readStoredCollapsed(): boolean {
    if (typeof window === 'undefined') {
        return false;
    }

    return window.localStorage.getItem(STORAGE_KEY) === '1';
}

const state = reactive({
    collapsed: readStoredCollapsed(),
    drawerOpen: false,
});

if (typeof window !== 'undefined') {
    watch(
        () => state.collapsed,
        (value) => {
            window.localStorage.setItem(STORAGE_KEY, value ? '1' : '0');
        },
    );
}

export function useNavState() {
    function toggleCollapsed(): void {
        state.collapsed = !state.collapsed;
    }

    function openDrawer(): void {
        state.drawerOpen = true;
    }

    function closeDrawer(): void {
        state.drawerOpen = false;
    }

    return { state, toggleCollapsed, openDrawer, closeDrawer };
}
