import { __ } from '@wordpress/i18n'
import {
  endOfMonth,
  endOfQuarter,
  endOfToday,
  endOfWeek,
  endOfYear,
  endOfYesterday,
  startOfDay,
  startOfMonth,
  startOfQuarter,
  startOfToday,
  startOfWeek,
  startOfYear,
  startOfYesterday,
  sub
} from 'date-fns'

const monday = 1
const firstDayOfTheWeek = parseInt(window.koko_analytics.start_of_week, 10) || monday

export default [
  {
    key: 'custom',
    label: __('Custom', 'koko-analytics')
  },
  {
    key: 'today',
    label: __('Today', 'koko-analytics'),
    dates: () => {
      const startDate = startOfToday()
      const endDate = endOfToday()
      return { startDate, endDate }
    }
  },
  {
    key: 'yesterday',
    label: __('Yesterday', 'koko-analytics'),
    dates: () => {
      const startDate = startOfYesterday()
      const endDate = endOfYesterday()
      return { startDate, endDate }
    }
  },
  {
    key: 'this_week',
    label: __('This week', 'koko-analytics'),
    dates: () => {
      const now = new Date()
      const startDate = startOfWeek(now, { weekStartsOn: firstDayOfTheWeek })
      const endDate = endOfWeek(now, { weekStartsOn: firstDayOfTheWeek })
      return { startDate, endDate }
    }
  },
  {
    key: 'last_week',
    label: __('Last week', 'koko-analytics'),
    dates: () => {
      const today = new Date()
      const lastWeekToday = sub(today, { weeks: 1 })
      const startDate = startOfWeek(lastWeekToday, {
        weekStartsOn: firstDayOfTheWeek
      })
      const endDate = endOfWeek(lastWeekToday, {
        weekStartsOn: firstDayOfTheWeek
      })
      return { startDate, endDate }
    }
  },
  {
    key: 'last_28_days',
    label: __('Last 28 days', 'koko-analytics'),
    dates: () => {
      const today = new Date()
      const twentySevenDaysAgo = sub(today, { days: 27 })
      const startDate = startOfDay(twentySevenDaysAgo)
      const endDate = endOfToday()
      return { startDate, endDate }
    }
  },
  {
    key: 'this_month',
    label: __('This month', 'koko-analytics'),
    dates: () => {
      const today = new Date()
      const startDate = startOfMonth(today)
      const endDate = endOfMonth(today)
      return { startDate, endDate }
    }
  },
  {
    key: 'last_month',
    label: __('Last month', 'koko-analytics'),
    dates: () => {
      const today = new Date()
      const lastMonthToday = sub(today, { months: 1 })
      const startDate = startOfMonth(lastMonthToday)
      const endDate = endOfMonth(lastMonthToday)
      return { startDate, endDate }
    }
  },
  {
    key: 'this_quarter',
    label: __('This quarter', 'koko-analytics'),
    dates: () => {
      const today = new Date()
      const startDate = startOfQuarter(today)
      const endDate = endOfQuarter(today)
      return { startDate, endDate }
    }
  },
  {
    key: 'last_quarter',
    label: __('Last quarter', 'koko-analytics'),
    dates: () => {
      const today = new Date()
      const startOfThisQuarter = startOfQuarter(today)
      const insideLastQuarter = sub(startOfThisQuarter, { weeks: 1 })
      const startDate = startOfQuarter(insideLastQuarter)
      const endDate = endOfQuarter(insideLastQuarter)
      return { startDate, endDate }
    }
  },
  {
    key: 'this_year',
    label: __('This year', 'koko-analytics'),
    dates: () => {
      const today = new Date()
      const startDate = startOfYear(today)
      const endDate = endOfYear(today)
      return { startDate, endDate }
    }
  },
  {
    key: 'last_year',
    label: __('Last year', 'koko-analytics'),
    dates: () => {
      const today = new Date()
      const lastYearToday = sub(today, { years: 1 })
      const startDate = startOfYear(lastYearToday)
      const endDate = endOfYear(lastYearToday)
      return { startDate, endDate }
    }
  }
]
