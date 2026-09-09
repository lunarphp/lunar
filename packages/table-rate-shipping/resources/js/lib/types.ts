export type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
    from: number | null;
    to: number | null;
    total: number;
};

export type TableColumn = { key: string; label: string; width?: string; align?: 'left' | 'right' | 'center' };

export type RowAction = {
    key: string;
    label: string;
    icon?: string | null;
    method: string;
    primary: boolean;
    confirmation?: string | null;
};

export type ExtensionFilter = { key: string; label: string; component: string | null; options: Record<string, string> };

export type CurrencyOption = { id: number; code: string; name: string; decimal_places: number; default: boolean };

export type NamedOption = { id: number; name: string };

/** The step a money input takes so a zero-decimal currency accepts whole units only. */
export const stepFor = (currency: { decimal_places: number }): string =>
    (currency.decimal_places > 0 ? `0.${'0'.repeat(currency.decimal_places - 1)}1` : '1');
