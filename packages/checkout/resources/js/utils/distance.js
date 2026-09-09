// Great-circle distance in metres between two { latitude, longitude } points
// (haversine). Straight line, not a route: enough to rank a few branches.
export function distanceBetween(a, b) {
  const rad = (deg) => (deg * Math.PI) / 180
  const earth = 6371008.8

  const dLat = rad(b.latitude - a.latitude)
  const dLng = rad(b.longitude - a.longitude)
  const h =
    Math.sin(dLat / 2) ** 2 + Math.cos(rad(a.latitude)) * Math.cos(rad(b.latitude)) * Math.sin(dLng / 2) ** 2

  return 2 * earth * Math.asin(Math.sqrt(h))
}

// "2.3 miles", "12 miles", "0.8 km", "45 km": one decimal under ten, whole
// numbers above, so the column reads at a glance.
export function formatDistance(metres, unit = 'km') {
  const value = unit === 'mi' ? metres / 1609.344 : metres / 1000
  const shown = value < 10 ? Math.round(value * 10) / 10 : Math.round(value)
  const label = unit === 'mi' ? (shown === 1 ? 'mile' : 'miles') : 'km'

  return `${shown} ${label}`
}
