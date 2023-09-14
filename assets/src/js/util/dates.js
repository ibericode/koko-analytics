import { __ } from '@wordpress/i18n'

const monthsFull = [
  __('January', 'koko-analytics' ),
  __('February', 'koko-analytics' ),
  __('March', 'koko-analytics' ),
  __('April', 'koko-analytics' ),
  __('May', 'koko-analytics' ),
  __('June', 'koko-analytics' ),
  __('July', 'koko-analytics' ),
  __('August', 'koko-analytics' ),
  __('September', 'koko-analytics' ),
  __('October', 'koko-analytics' ),
  __('November', 'koko-analytics' ),
  __('December', 'koko-analytics' ),
]
const monthsShort = [
  __( 'Jan', 'koko-analytics' ),
  __( 'Feb', 'koko-analytics' ),
  __( 'Mar', 'koko-analytics' ),
  __( 'Apr', 'koko-analytics' ),
  __( 'May', 'koko-analytics' ),
  __( 'Jun', 'koko-analytics' ),
  __( 'Jul', 'koko-analytics' ),
  __( 'Aug', 'koko-analytics' ),
  __( 'Sep', 'koko-analytics' ),
  __( 'Oct', 'koko-analytics' ),
  __( 'Nov', 'koko-analytics' ),
  __( 'Dec', 'koko-analytics' )
]
const daysFull = [
  __( 'Sunday', 'koko-analytics' ),
  __( 'Monday', 'koko-analytics' ),
  __( 'Tuesday', 'koko-analytics' ),
  __( 'Wednesday', 'koko-analytics' ),
  __( 'Thursday', 'koko-analytics' ),
  __( 'Friday', 'koko-analytics' ),
  __( 'Saturday', 'koko-analytics' )
]
const daysShort =  [
  __( 'Sun', 'koko-analytics' ),
  __( 'Mon', 'koko-analytics' ),
  __( 'Tue', 'koko-analytics' ),
  __( 'Wed', 'koko-analytics' ),
  __( 'Thr', 'koko-analytics' ),
  __( 'Fri', 'koko-analytics' ),
  __( 'Sat', 'koko-analytics' )
];

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
 * Return a formatted string from a Date object
 * Accepts the same format string as PHP's DateTimeInterface::format function.
 *
 * @param date {Date} Date instance
 * @param format {string} "Y-m-d H:i:s" or similar PHP-style date format string
 * @param opts
 */
function format (date, format, opts) {
  let string = '',
    mo = date.getMonth(),   // month (0-11)
    m1 = mo + 1,			    // month (1-12)
    dow = date.getDay(),    // day of week (0-6)
    d = date.getDate(),     // day of the month (1-31)
    y = date.getFullYear() // 1999 or 2003

  if (opts && false === opts.day) {
    format = format.replace(/[djlwD]/, '');
  }
  if (opts && false === opts.year) {
    format = format.replace(/[yY]/, '');
  }

  format = format.replace('//', '/').replace(/^[-/, ]/, '').replace(/[-/, ]$/, '')

  for (let i = 0, len = format.length; i < len; i++) {
    switch (format[i]) {
      case 'j': // Day of the month without leading zeros  (1 to 31)
        string += d
        break

      case 'd': // Day of the month, 2 digits with leading zeros (01 to 31)
        string += (d < 10) ? '0' + d : d
        break

      case 'l': // (lowercase 'L') A full textual representation of the day of the week
        string += daysFull[dow]
        break

      case 'w': // Numeric representation of the day of the week (0=Sunday,1=Monday,...6=Saturday)
        string += dow
        break

      case 'D': // A textual representation of a day, three letters
        string += daysShort[dow]
        break

      case 'm': // Numeric representation of a month, with leading zeros (01 to 12)
        string += (m1 < 10) ? '0' + m1 : m1
        break

      case 'n': // Numeric representation of a month, without leading zeros (1 to 12)
        string += m1
        break

      case 'F': // A full textual representation of a month, such as January or March
        string += monthsFull[mo]
        break

      case 'M': // A short textual representation of a month, three letters (Jan - Dec)
        string += monthsShort[mo]
        break

      case 'Y': // A full numeric representation of a year, 4 digits (1999 OR 2003)
        string += y
        break

      case 'y': // A two digit representation of a year (99 OR 03)
        string += y.toString().slice(-2)
        break

      default: // spaces, commas, slashes, other delims
        string += format[i]
    }
  }

  return string
}

/**
 * Parse a ISO8601 date string (YYYY-MM-DD) into a Date object.
 *
 * @param v {string}
 * @returns {Date|null}
 */
function parseISO8601 (v) {
  if (!v) return null;

  const parts = v.split('-')
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

export { format, isLastDayOfMonth, parseISO8601, toISO8601 }
