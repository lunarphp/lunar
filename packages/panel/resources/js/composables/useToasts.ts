import { ref, type Ref } from 'vue';

export type ToastTone = 'success' | 'error' | 'info';

export interface Toast {
    id: number;
    tone: ToastTone;
    message: string;
    /** Milliseconds before auto-dismiss; 0 keeps the toast until closed. */
    duration: number;
}

export interface ToastApi {
    toasts: Ref<Toast[]>;
    success: (message: string, duration?: number) => number;
    error: (message: string, duration?: number) => number;
    info: (message: string, duration?: number) => number;
    dismiss: (id: number) => void;
    clear: () => void;
}

// Errors stay until dismissed; confirmations and notes clear themselves.
const DEFAULT_DURATIONS: Record<ToastTone, number> = {
    success: 6000,
    info: 6000,
    error: 0,
};

// Module-level store so any page, component, or add-on bundle pushes into the
// same stack, and toasts survive layout swaps between visits.
const toasts = ref<Toast[]>([]);
let nextId = 0;

function push(tone: ToastTone, message: string, duration?: number): number {
    const id = ++nextId;

    toasts.value = [...toasts.value, { id, tone, message, duration: duration ?? DEFAULT_DURATIONS[tone] }];

    return id;
}

function dismiss(id: number): void {
    toasts.value = toasts.value.filter((toast) => toast.id !== id);
}

export function useToasts(): ToastApi {
    return {
        toasts,
        success: (message, duration?) => push('success', message, duration),
        error: (message, duration?) => push('error', message, duration),
        info: (message, duration?) => push('info', message, duration),
        dismiss,
        clear: () => {
            toasts.value = [];
        },
    };
}

export interface ServerFlash {
    success?: string | null;
    error?: string | null;
    info?: string | null;
}

// The flash prop object is a fresh reference per server response, so identity
// tracking both dedupes double-fires (two Toaster instances during a layout
// swap) and still re-toasts an identical message on a repeat save.
let handledFlash: ServerFlash | null = null;

export function pushServerFlash(flash: ServerFlash | null | undefined): void {
    if (!flash || flash === handledFlash) {
        return;
    }

    handledFlash = flash;

    if (flash.success) {
        push('success', flash.success);
    }

    if (flash.error) {
        push('error', flash.error);
    }

    if (flash.info) {
        push('info', flash.info);
    }
}
