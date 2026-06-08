/**
 * Format an amount given in minor units (e.g. cents) as a currency string.
 * Lunar stores prices as integers, so the components receive minor units.
 *
 * @param {number} minor
 * @param {string} currency ISO 4217 code
 */
export function money(minor, currency = 'USD') {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency,
  }).format((minor || 0) / 100)
}
