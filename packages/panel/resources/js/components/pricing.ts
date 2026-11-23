import type { CurrencyOption, PriceValue } from './PricingEditor.vue';
import type { ConflictField } from './DraftConflictDialog.vue';

type Translate = (key: string) => string;

/**
 * An amount in integer minor units as money in the currency, e.g. `$33.00`;
 * a code Intl cannot resolve falls back to `33.00 XYZ`.
 */
export function formatMoney(minor: number, currency: CurrencyOption): string {
    const major = minor / 10 ** currency.decimal_places;

    try {
        return new Intl.NumberFormat(undefined, {
            style: 'currency',
            currency: currency.code,
            currencyDisplay: 'narrowSymbol',
            minimumFractionDigits: currency.decimal_places,
            maximumFractionDigits: currency.decimal_places,
        }).format(major);
    } catch {
        return `${major.toFixed(currency.decimal_places)} ${currency.code}`;
    }
}

/**
 * A currency's price list as the pricing editor describes it: each row's
 * amount, then its list price, customer group and minimum quantity where
 * they apply. A row's parts join with a middle dot, as the dialog joins a
 * translated field's languages, and rows join with a semicolon.
 */
export function formatPriceList(
    rows: PriceValue[],
    currency: CurrencyOption,
    customerGroups: { id: number; name: string }[],
    t: Translate,
): string {
    return rows
        .map((row) => {
            const parts = [row.price === null ? t('drafts.empty') : formatMoney(row.price, currency)];

            if (row.list_price !== null) {
                parts.push(`${t('pricing.compare_at')} ${formatMoney(row.list_price, currency)}`);
            }

            if (row.customer_group_id !== null) {
                parts.push(customerGroups.find((group) => group.id === row.customer_group_id)?.name ?? `#${row.customer_group_id}`);
            }

            if (row.min_quantity > 1) {
                parts.push(`${t('pricing.tier_min_prefix')} ${row.min_quantity}`);
            }

            return parts.join(' · ');
        })
        .join('; ');
}

/**
 * The conflict dialog's presentation for a `{prefix}{currency code}` price
 * list key, or undefined for any other key.
 */
export function priceConflictField(
    key: string,
    prefix: string,
    currencies: CurrencyOption[],
    customerGroups: { id: number; name: string }[],
    t: Translate,
): ConflictField | undefined {
    const currency = key.startsWith(prefix) ? currencies.find((option) => `${prefix}${option.code}` === key) : undefined;

    if (!currency) {
        return undefined;
    }

    return {
        format: (value) => (Array.isArray(value) ? formatPriceList(value as PriceValue[], currency, customerGroups, t) : String(value)),
    };
}
