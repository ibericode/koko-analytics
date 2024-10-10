/**
 * @param {Date} a
 * @returns {boolean}
 */
export function isLastDayOfMonth(a) {
  let b = new Date(a);
  b.setDate(b.getDate() + 1);
  return a.getMonth() !== b.getMonth()
}


/**
 * Parse a ISO8601 date string (YYYY-MM-DD) into a Date object.
 *
 * @param v {string}
 * @returns {Date|null}
 */
export function parseISO8601 (v) {
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
export function toISO8601(d) {
   return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`
}

/**
 * @param {string|Date} d
 * @param {object?} options
 * @returns {string}
 */
export function format(d, options) {
  d = typeof d === 'string' ? parseISO8601(d) : d;
  options = options ? options : { day: 'numeric', month: 'short', year: 'numeric' };
  try {
    return (new Intl.DateTimeFormat(undefined, options)).format(d);
  } catch (e) {
    // ignore error
  }

  return d.toLocaleDateString()
}
