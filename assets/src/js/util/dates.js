const monthsFull = Array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December')
const monthsShort = Array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec')
const daysFull = Array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday')
const daysShort =  Array('Sun', 'Mon', 'Tue', 'Wed', 'Thr', 'Fri', 'Sat');

function isLastDayOfMonth (_date) {
  const d = new Date(_date.getFullYear(), _date.getMonth(), 1)
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

  opts = Object.assign({
    day: true,
    year: true
  }, opts ?? {})

  for (let i = 0, len = format.length; i < len; i++) {
    switch (format[i]) {
      case 'j': // Day of the month without leading zeros  (1 to 31)
        string += opts.day ? d : ''
        break

      case 'd': // Day of the month, 2 digits with leading zeros (01 to 31)
        if (opts.day) {
          string += (d < 10) ? '0' + d : d
        }
        break

      case 'l': // (lowercase 'L') A full textual representation of the day of the week
        string += opts.day ? daysFull[dow] : ''
        break

      case 'w': // Numeric representation of the day of the week (0=Sunday,1=Monday,...6=Saturday)
        string += opts.day ? dow : ''
        break

      case 'D': // A textual representation of a day, three letters
        string += opts.day ? daysShort[dow] : ''
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
        string += opts.year ? y : ''
        break

      case 'y': // A two digit representation of a year (99 OR 03)
        string += opts.year ? y.toString().slice(-2) : ''
        break

      case 'c': // ISO 8601 date (eg: 2012-11-20T18:05:54.944Z)
        string += date.toISOString()
        break

      default:
        string += format[i]
    }
  }

  return string.replace(' ,', '').replace('//', '/').replace(/[-,/] ?$/, '').replace(/^[-,/]*/, '')
}

function parseISO8601 (v) {
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

export { format, isLastDayOfMonth, parseISO8601 }
