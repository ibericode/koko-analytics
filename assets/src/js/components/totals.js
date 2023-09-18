import React, {useState, useEffect} from 'react'
import '../../sass/totals.scss'
import { formatLargeNumber, formatPercentage } from '../util/numbers.js'
import {toISO8601} from '../util/dates'
import {request} from '../util/api'
import Realtime from './realtime.js'
import { __ } from '@wordpress/i18n'

export default function Totals({ startDate, endDate }) {
  let [totals, setTotals] = useState({ visitors: 0, pageviews: 0, visitors_change: 0, pageviews_change: 0, visitors_change_rel: 0.00, pageviews_change_rel: 0.00 })

  function loadData() {
    // fetch stats for current period
    request('/totals', {
      body: {
        start_date: toISO8601(startDate),
        end_date: toISO8601(endDate)
      }
    }).then(setTotals)
  }

  useEffect(() => {
    const interval = setInterval(() => {
      const now = new Date()
      if (startDate < now && endDate > now) {
        loadData()
      }
    }, 60000)
    return () => {
      clearInterval(interval)
    }
  }, [startDate, endDate])

  useEffect(loadData, [startDate, endDate])

  return (
    <div className='totals-container'>
      <div className='totals-box koko-fade'>
        <div className='totals-label'>{__('Total visitors', 'koko-analytics')}</div>
        <div className='totals-amount'>{formatLargeNumber(totals.visitors)} {totals.visitors_change_rel !== null ? <span
          className={totals.visitors_change_rel > 0 ? 'up' : parseInt(totals.visitors_change_rel) === 0 ? 'neutral' : 'down'}
        >{formatPercentage(totals.visitors_change_rel)}
          </span> : ''}
        </div>
        <div className='totals-compare'>
          <span>{formatLargeNumber(Math.abs(totals.visitors_change))} {totals.visitors_change > 0 ? __('more than previous period', 'koko-analytics') : __('less than previous period', 'koko-analytics')}</span>
        </div>
      </div>
      <div className='totals-box koko-fade'>
        <div className='totals-label'>{__('Total pageviews', 'koko-analytics')}</div>
        <div className='totals-amount'>{formatLargeNumber(totals.pageviews)} {totals.pageviews_change_rel !== null ? <span
          className={totals.pageviews_change > 0 ? 'up' : parseInt(totals.pageviews_change) === 0 ? 'neutral' : 'down'}
        >{formatPercentage(totals.pageviews_change_rel)}
          </span> : ''}
        </div>
        <div className='totals-compare'>
          <span>{formatLargeNumber(Math.abs(totals.pageviews_change))} {totals.pageviews_change > 0 ? __('more than previous period', 'koko-analytics') : __('less than previous period', 'koko-analytics')}</span>
        </div>
      </div>
      <Realtime />
    </div>
  )
}

