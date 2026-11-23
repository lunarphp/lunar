/**
 * Validation errors for array fields arrive keyed per item (`countries.2`,
 * `rates.0.name`), so a template reading `form.errors.countries` never sees
 * them. Returns the error on the field itself or, failing that, the first
 * error on any of its items.
 */
export function firstError(errors: object, field: string): string | undefined {
    const messages = errors as Record<string, string | undefined>;

    return messages[field] ?? Object.entries(messages).find(([key]) => key.startsWith(`${field}.`))?.[1];
}
