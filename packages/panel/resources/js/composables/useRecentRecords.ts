import { ref } from 'vue';
import type { SearchResult } from '../types/search';

/** Enough to jump back to what you were just working on, without a scrollbar. */
const LIMIT = 8;

const STORAGE_PREFIX = 'lunar-panel-recent-records';

const records = ref<SearchResult[]>([]);

function storageKey(userId: string | number): string {
    return `${STORAGE_PREFIX}:${userId}`;
}

function read(userId: string | number): SearchResult[] {
    if (typeof window === 'undefined') {
        return [];
    }

    try {
        const raw = window.localStorage.getItem(storageKey(userId));
        const parsed = raw ? JSON.parse(raw) : [];

        return Array.isArray(parsed) ? (parsed as SearchResult[]) : [];
    } catch {
        return [];
    }
}

function write(userId: string | number, rows: SearchResult[]): void {
    if (typeof window === 'undefined') {
        return;
    }

    try {
        window.localStorage.setItem(storageKey(userId), JSON.stringify(rows));
    } catch {
        // A full or unavailable store just means no history; never break navigation over it.
    }
}

/**
 * The staff member's recently opened records, kept per user in localStorage so
 * the palette has something useful to show before the first keystroke.
 */
export function useRecentRecords(userId: string | number) {
    // Re-read on every call rather than caching: the ref is shared so the
    // layout and the palette stay in step, and a stale cache would outlive a
    // change of staff member.
    records.value = read(userId);

    function remember(record: SearchResult): void {
        records.value = [
            record,
            ...records.value.filter((row) => !(row.kind === record.kind && row.id === record.id)),
        ].slice(0, LIMIT);

        write(userId, records.value);
    }

    /** Drop a record that no longer resolves, so a deleted row stops being offered. */
    function forget(kind: string, id: string | number): void {
        records.value = records.value.filter((row) => !(row.kind === kind && row.id === id));

        write(userId, records.value);
    }

    return { records, remember, forget };
}
