function isLastDayOfMonth (_date) {
  const d = new Date(_date.getFullYear(), _date.getMonth(), 1)
  d.setMonth(d.getMonth() + 1)
  d.setDate(0)
  return d.getDate() === _date.getDate()
}

export { isLastDayOfMonth }
