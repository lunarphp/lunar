// Value helpers shared by the page forms that host form slices.

// JSON round-trip rather than structuredClone: form values are JSON-shaped
// by construction, and this also unwraps Vue reactive proxies safely.
export function clone<T>(value: T): T {
    return value === undefined ? value : (JSON.parse(JSON.stringify(value)) as T);
}

// Mirrors the server's comparison: object keys sort, list order matters.
export function normalize(value: unknown): unknown {
    if (Array.isArray(value)) {
        return value.map(normalize);
    }

    if (value && typeof value === 'object') {
        return Object.fromEntries(
            Object.entries(value as Record<string, unknown>)
                .sort(([a], [b]) => (a < b ? -1 : a > b ? 1 : 0))
                .map(([key, entry]) => [key, normalize(entry)]),
        );
    }

    return value;
}

export function encode(value: unknown): string {
    return JSON.stringify(normalize(value)) ?? 'undefined';
}
