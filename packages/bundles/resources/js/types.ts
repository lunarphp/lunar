export interface VariantRow {
    id: number;
    public_id: string;
    sku: string | null;
    name: string;
    option: string | null;
    thumbnail: string | null;
    edit_url: string;
}

export interface BundleComponentRow {
    id: number;
    public_id: string;
    group_id: number | null;
    quantity: number;
    default: boolean;
    position: number;
    variant: VariantRow;
}

export interface BundleGroupRow {
    id: number;
    public_id: string;
    name: Record<string, string>;
    label: string;
    min_selections: number;
    max_selections: number;
    position: number;
}

export interface DerivedPrice {
    currency: string;
    price: string | null;
    list_price: string | null;
    missing: string[];
}

export interface BundleDefinition {
    id: number;
    pricing: 'fixed' | 'components';
    discount_percentage: number | null;
    configurable: boolean;
    components: BundleComponentRow[];
    groups: BundleGroupRow[];
    availability: { available: number; unlimited: boolean };
    prices: DerivedPrice[];
}

export interface BundleSummary {
    variant: VariantRow;
    defined: boolean;
    bundle: BundleDefinition | null;
    default_language: string;
    urls: {
        show: string;
        define: string;
        destroy: string;
        components: string;
        groups: string;
        search: string;
    };
}

/** What the sync endpoints accept; every call sends the complete list. */
export interface ComponentPayload {
    variant_id: number;
    quantity: number;
    group_id: number | null;
    default: boolean;
    position: number;
}

export interface GroupPayload {
    id: number | null;
    name: Record<string, string>;
    min_selections: number;
    max_selections: number;
    position: number;
}

export const panelPath = (props: Record<string, unknown>): string =>
    (props.panel as { path?: string } | undefined)?.path ?? 'panel';
