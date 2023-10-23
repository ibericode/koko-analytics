/**
 * @param _date {Date}
 * @returns {boolean}
 */
function isLastDayOfMonth (_date) {
  const d = new Date(_date.getFullYear(), _date.getMonth() , 1)
  d.setMonth(d.getMonth() + 1)
  d.setDate(0)
  return d.getDate() === _date.getDate()
}

/**
 * Parse a ISO8601 date string (YYYY-MM-DD) into a Date object.
 *
 * @param v {string}
 * @returns {Date|null}
 */
function parseISO8601 (v) {
  if (v === null) {
    return null;
  }
  const parts = v.split('-')
  if (parts.length === 2) {
    parts.push('1')
  }

  if (parts.length !== 3) {
    return null
  }

  let [y, m, d] = parts.map(v => parseInt(v, 10))
  if (y < 1000) {
    y += 2000
  }

  if (y < 2000 || y > 3000 || m < 1 || m > 12 || d < 1 || d > 31) {
    return null
  }

  return new Date(y, m - 1, d)
}

/**
 * Pad a number with zeroes if it's below 10
 *
 * @param {int} d
 * @returns {string}
 */
function pad(d) {
  return d < 10 ? '0' + d : d;
}

/**
 * Returns a string representing the given Date object in YYYY-MM-DD format
 *
 * @param {Date} d
 * @returns {string}
 */
function toISO8601(d) {
   return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`
}

/**
 * @param {string|Date} d
 * @param {object?} options
 * @returns {string}
 */
function format(d, options) {
  if (typeof d === 'string') {
    d = parseISO8601(d)
  }

  try {
    return (new Intl.DateTimeFormat(undefined, options ?? { day: 'numeric', month: 'short', year: 'numeric' })).format(d)
  } catch {
    // ignore error
  }

  return d.toLocaleDateString()
}

export { isLastDayOfMonth, parseISO8601, toISO8601, format }
