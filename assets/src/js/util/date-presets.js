import { __ } from '@wordpress/i18n'
import {
  endOfMonth,
  endOfQuarter,
  endOfToday,
  endOfWeek,
  endOfYesterday,
  startOfDay,
  startOfMonth,
  startOfQuarter,
  startOfWeek,
  startOfYesterday,
  sub
} from 'date-fns'

const weekStartsOn = window.koko_analytics.startOfWeek;

function now() {
  return new Date()
}

export default [
  {
    key: 'custom',
    label: __('Custom', 'koko-analytics')
  },
  {
    key: 'today',
    label: __('Today', 'koko-analytics'),
    dates: () => {
      let startDate = new Date(),
        endDate = new Date()
      startDate.setHours(0, 0, 0)
      endDate.setHours(23, 59, 59)
      return { startDate, endDate }
    }
  },
  {
    key: 'yesterday',
    label: __('Yesterday', 'koko-analytics'),
    dates: () => {
      return { startDate: startOfYesterday(), endDate: endOfYesterday() }
    }
  },
  {
    key: 'this_week',
    label: __('This week', 'koko-analytics'),
    dates: () => {
      return { startDate: startOfWeek(now(), { weekStartsOn: weekStartsOn }), endDate: endOfWeek(now(), { weekStartsOn: weekStartsOn }) }
    }
  },
  {
    key: 'last_week',
    label: __('Last week', 'koko-analytics'),
    dates: () => {
      const lastWeekToday = sub(now(), { weeks: 1 })
      return { startDate: startOfWeek(lastWeekToday, { weekStartsOn }), endDate: endOfWeek(lastWeekToday, { weekStartsOn }) }
    }
  },
  {
    key: 'last_28_days',
    label: __('Last 28 days', 'koko-analytics'),
    dates: () => {
      const twentySevenDaysAgo = sub(now(), { days: 27 })
      return { startDate: startOfDay(twentySevenDaysAgo), endDate: endOfToday() }
    }
  },
  {
    key: 'this_month',
    label: __('This month', 'koko-analytics'),
    dates: () => {
      return { startDate: startOfMonth(now()), endDate: endOfMonth(now()) }
    }
  },
  {
    key: 'last_month',
    label: __('Last month', 'koko-analytics'),
    dates: () => {
      const lastMonthToday = sub(now(), { months: 1 })
      return { startDate: startOfMonth(lastMonthToday), endDate: endOfMonth(lastMonthToday) }
    }
  },
  {
    key: 'this_quarter',
    label: __('This quarter', 'koko-analytics'),
    dates: () => {
      return { startDate: startOfQuarter(now()), endDate: endOfQuarter(now()) }
    }
  },
  {
    key: 'last_quarter',
    label: __('Last quarter', 'koko-analytics'),
    dates: () => {
      const startOfThisQuarter = startOfQuarter(now())
      const insideLastQuarter = sub(startOfThisQuarter, { weeks: 1 })
      return { startDate: startOfQuarter(insideLastQuarter), endDate: endOfQuarter(insideLastQuarter) }
    }
  },
  {
    key: 'this_year',
    label: __('This year', 'koko-analytics'),
    dates: () => {
      const y = now().getFullYear()
      return { startDate: new Date(y, 0, 1), endDate: new Date(y, 11, 31) }
    }
  },
  {
    key: 'last_year',
    label: __('Last year', 'koko-analytics'),
    dates: () => {
      const y = now().getFullYear() - 1
      return { startDate: new Date(y, 0, 1), endDate:  new Date(y, 11, 31) }
    }
  }
]
