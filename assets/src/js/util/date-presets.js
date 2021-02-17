import { __ } from '@wordpress/i18n'
const startOfWeek = parseInt(window.koko_analytics.start_of_week)

export default [
  {
    key: 'custom',
    label: __('Custom', 'koko-analytics')
  },
  {
    key: 'today',
    label: __('Today', 'koko-analytics'),
    dates: () => {
      const now = new Date()
      const startDate = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 0, 0, 0)
      const endDate = new Date(now.getFullYear(), now.getMonth(), startDate.getDate(), 23, 59, 59)
      return { startDate, endDate }
    }
  },
  {
    key: 'yesterday',
    label: __('Yesterday', 'koko-analytics'),
    dates: () => {
      const now = new Date()
      const startDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 1, 0, 0, 0)
      const endDate = new Date(now.getFullYear(), now.getMonth(), startDate.getDate(), 23, 59, 59)
      return { startDate, endDate }
    }
  },
  {
    key: 'this_week',
    label: __('This week', 'koko-analytics'),
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
    key: 'last_week',
    label: __('Last week', 'koko-analytics'),
    dates: () => {
      const now = new Date()
      let d = now.getDate() - now.getDay() + startOfWeek
      if (now.getDay() < startOfWeek) {
        d = d - 7
      }

      const startDate = new Date(now.getFullYear(), now.getMonth(), d - 7, 0, 0, 0)
      const endDate = new Date(now.getFullYear(), startDate.getMonth(), startDate.getDate() + 6, 23, 59, 59)
      return { startDate, endDate }
    }
  },
  {
    key: 'last_28_days',
    label: __('Last 28 days', 'koko-analytics'),
    dates: () => {
      const now = new Date()
      const startDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 27, 0, 0, 0)
      const endDate = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59)
      return { startDate, endDate }
    }
  },
  {
    key: 'this_month',
    label: __('This month', 'koko-analytics'),
    dates: () => {
      const now = new Date()
      const startDate = new Date(now.getFullYear(), now.getMonth(), 1, 0, 0, 0)
      const endDate = new Date(startDate.getFullYear(), startDate.getMonth() + 1, 0, 23, 59, 59)
      return { startDate, endDate }
    }
  },
  {
    key: 'last_month',
    label: __('Last month', 'koko-analytics'),
    dates: () => {
      const now = new Date()
      const startDate = new Date(now.getFullYear(), now.getMonth() - 1, 1, 0, 0, 0)
      const endDate = new Date(startDate.getFullYear(), startDate.getMonth() + 1, 0, 23, 59, 59)
      return { startDate, endDate }
    }
  },
  {
    key: 'this_quarter',
    label: __('This quarter', 'koko-analytics'),
    dates: () => {
      const now = new Date()
      const startDate = new Date(now.getFullYear(), (Math.ceil((now.getMonth() + 1) / 3) - 1) * 3, 1, 0, 0, 0)
      const endDate = new Date(startDate.getFullYear(), startDate.getMonth() + 3, 0, 23, 59, 59)
      return { startDate, endDate }
    }
  },
  {
    key: 'last_quarter',
    label: __('Last quarter', 'koko-analytics'),
    dates: () => {
      const now = new Date()
      const startDate = new Date(now.getFullYear(), (Math.ceil((now.getMonth() + 1) / 3) - 1) * 3 - 3, 1, 0, 0, 0)
      const endDate = new Date(startDate.getFullYear(), startDate.getMonth() + 3, 0, 23, 59, 59)
      return { startDate, endDate }
    }
  },
  {
    key: 'this_year',
    label: __('This year', 'koko-analytics'),
    dates: () => {
      const now = new Date()
      const startDate = new Date(now.getFullYear(), 0, 1, 0, 0, 0)
      const endDate = new Date(startDate.getFullYear(), 12, 0, 23, 59, 59)
      return { startDate, endDate }
    }
  },
  {
    key: 'last_year',
    label: __('Last year', 'koko-analytics'),
    dates: () => {
      const now = new Date()
      const startDate = new Date(now.getFullYear() - 1, 0, 1, 0, 0, 0)
      const endDate = new Date(startDate.getFullYear(), 12, 0, 23, 59, 59)
      return { startDate, endDate }
    }
  }
]
