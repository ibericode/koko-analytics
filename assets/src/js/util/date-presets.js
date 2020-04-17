const i18n = window.koko_analytics.i18n;
const startOfWeek = window.koko_analytics.start_of_week

export default [
  {
    key: 'last_28_days',
    label: i18n['Last 28 days'],
    dates: () => {
      const now = new Date()
      const startDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 28, 0, 0, 0)
      const endDate = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59)
      return { startDate, endDate }
    }
  },
  {
    key: 'today',
    label: i18n['Today'],
    dates: () => {
      const now = new Date()
      const startDate = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 0, 0, 0)
      const endDate = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59)
      return { startDate, endDate }
    }
  },
  {
    key: 'this_week',
    label: i18n['This week'],
    dates: () => {
      const now = new Date()
      let d = now.getDate() - now.getDay() + startOfWeek
      if (now.getDay() < startOfWeek) {
        d = d - 7
      }

      const startDate = new Date(now.getFullYear(), now.getMonth(), d, 0, 0, 0)
      const endDate = new Date(now.getFullYear(), startDate.getMonth(), startDate.getDate() + 6, 23, 59, 59)
      return { startDate, endDate }
    }
  },
  {
    key: 'this_month',
    label: i18n['This month'],
    dates: () => {
      const now = new Date()
      const startDate = new Date(now.getFullYear(), now.getMonth(), 1, 0, 0, 0)
      const endDate = new Date(startDate.getFullYear(), startDate.getMonth() + 1, 0, 23, 59, 59)
      return { startDate, endDate }
    }
  },
  {
    key: 'this_quarter',
    label: i18n['This quarter'],
    dates: () => {
      const now = new Date()
      const startDate = new Date(now.getFullYear(), (Math.ceil((now.getMonth() + 1) / 3) - 1) * 3, 1, 0, 0, 0)
      const endDate = new Date(startDate.getFullYear(), startDate.getMonth() + 3, 0, 23, 59, 59)
      return { startDate, endDate }
    }
  },
  {
    key: 'this_year',
    label: i18n['This year'],
    dates: () => {
      const now = new Date()
      const startDate = new Date(now.getFullYear(), 0, 1, 0, 0, 0)
      const endDate = new Date(startDate.getFullYear(), 12, 0, 23, 59, 59)
      return { startDate, endDate }
    }
  }
]
