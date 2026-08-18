import { ref, type Ref } from 'vue';

export type ThemePreference = 'light' | 'dark' | 'system';

const STORAGE_KEY = 'lunar-panel-theme';

function readStoredTheme(): ThemePreference {
    if (typeof window === 'undefined') {
        return 'system';
    }

    const stored = window.localStorage.getItem(STORAGE_KEY);

    return stored === 'light' || stored === 'dark' || stored === 'system' ? stored : 'system';
}

function prefersDark(): boolean {
    return typeof window !== 'undefined' && window.matchMedia('(prefers-color-scheme: dark)').matches;
}

function applyTheme(value: ThemePreference): void {
    if (typeof document === 'undefined') {
        return;
    }

    const isDark = value === 'dark' || (value === 'system' && prefersDark());

    document.documentElement.classList.toggle('dark', isDark);
}

const theme: Ref<ThemePreference> = ref(readStoredTheme());

// Applied at module load so the correct class is set before first paint.
applyTheme(theme.value);

if (typeof window !== 'undefined') {
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        if (theme.value === 'system') {
            applyTheme(theme.value);
        }
    });
}

const ORDER: ThemePreference[] = ['light', 'dark', 'system'];

export function useTheme() {
    function setTheme(value: ThemePreference): void {
        theme.value = value;
        window.localStorage.setItem(STORAGE_KEY, value);
        applyTheme(value);
    }

    function cycleTheme(): void {
        const next = ORDER[(ORDER.indexOf(theme.value) + 1) % ORDER.length];
        setTheme(next);
    }

    return { theme, setTheme, cycleTheme };
}
