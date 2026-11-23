/**
 * Conversions between the instants the server sends and stores (ISO 8601
 * strings with an offset) and the zoneless `YYYY-MM-DDTHH:mm` value a native
 * `datetime-local` input reads and writes. The input always means the
 * browser's local time, so staff enter and see their own wall-clock time
 * while the server receives the matching instant.
 */

const pad = (value: number): string => String(value).padStart(2, '0');

/**
 * An instant as the `datetime-local` value for the browser's timezone. Empty
 * for a missing or unreadable value.
 */
export function toLocalInput(value: string | Date | null | undefined): string {
    if (!value) {
        return '';
    }

    const date = value instanceof Date ? value : new Date(value);

    if (Number.isNaN(date.getTime())) {
        return '';
    }

    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

/**
 * A `datetime-local` value, read as the browser's local time, as a UTC ISO
 * 8601 instant. Empty for a cleared or incomplete input.
 */
export function fromLocalInput(value: string): string {
    const match = /^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})/.exec(value);

    if (!match) {
        return '';
    }

    const [year, month, day, hour, minute] = match.slice(1).map(Number);

    return new Date(year, month - 1, day, hour, minute).toISOString();
}
