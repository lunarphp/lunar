// The panel's sanctioned non-Inertia transport, for endpoints whose responses
// have no home in Inertia's page-visit cycle (draft autosave, commit's 409
// conflict payload). Everything page-shaped stays on @inertiajs/vue3.

export interface DraftConflict {
    key: string;
    label: string;
    mine: unknown;
    base: unknown;
    theirs: unknown;
}

export class ValidationError extends Error {
    constructor(public readonly errors: Record<string, string[]>) {
        super('The request payload failed validation.');
        this.name = 'ValidationError';
    }
}

export class DraftConflictError extends Error {
    constructor(public readonly conflicts: DraftConflict[]) {
        super('The commit conflicts with concurrent changes.');
        this.name = 'DraftConflictError';
    }
}

export class HttpError extends Error {
    constructor(public readonly status: number) {
        super(`Request failed with status ${status}.`);
        this.name = 'HttpError';
    }
}

function xsrfToken(): string {
    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);

    return match ? decodeURIComponent(match[1]) : '';
}

async function request<T>(method: string, url: string, body?: unknown): Promise<T> {
    const response = await fetch(url, {
        method,
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-XSRF-TOKEN': xsrfToken(),
        },
        body: body === undefined ? undefined : JSON.stringify(body),
    });

    if (response.status === 204) {
        return null as T;
    }

    const payload = await response.json().catch(() => null);

    if (response.status === 422) {
        throw new ValidationError((payload?.errors ?? {}) as Record<string, string[]>);
    }

    if (response.status === 409) {
        throw new DraftConflictError((payload?.conflicts ?? []) as DraftConflict[]);
    }

    if (!response.ok) {
        throw new HttpError(response.status);
    }

    return payload as T;
}

export const http = {
    get: <T>(url: string): Promise<T> => request<T>('GET', url),
    post: <T>(url: string, body?: unknown): Promise<T> => request<T>('POST', url, body),
    put: <T>(url: string, body?: unknown): Promise<T> => request<T>('PUT', url, body),
    patch: <T>(url: string, body?: unknown): Promise<T> => request<T>('PATCH', url, body),
    delete: <T = null>(url: string): Promise<T> => request<T>('DELETE', url),
};
