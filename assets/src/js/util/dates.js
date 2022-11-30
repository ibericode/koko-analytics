function isLastDayOfMonth (_date) {
  const d = new Date(_date.getFullYear(), _date.getMonth(), 1)
  d.setMonth(d.getMonth() + 1)
  d.setDate(0)
  return d.getDate() === _date.getDate()
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

export { isLastDayOfMonth, parseISO8601 }
