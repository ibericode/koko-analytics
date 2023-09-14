import React, {useState, useEffect} from 'react'
import '../../sass/totals.scss'
import { formatLargeNumber, formatPercentage } from '../util/numbers.js'
import {toISO8601} from '../util/dates'
import {request} from '../util/api'
import Realtime from './realtime.js'
import { __ } from '@wordpress/i18n'

export default function Totals({ startDate, endDate }) {
  let [totals, setTotals] = useState([{ visitors: 0, pageviews: 0}, { visitors: 0, pageviews: 0}])

  function autoRefresh () {
    const now = new Date()
    if (startDate < now && endDate > now) {
      loadData()
    }
  }

  function loadData() {
    const diff = (endDate - startDate) + 1
    const previousStartDate = new Date(startDate - diff)
    const previousEndDate = new Date(endDate - diff)
    let p1 = { visitors: 0, pageviews: 0 };
    let p2 = { visitors: 0, pageviews: 0 }

    Promise.all([
      // fetch stats for current period
      request('/stats', {
        body: {
          start_date: toISO8601(startDate),
          end_date: toISO8601(endDate)
        }
      }).then(data => {
        data.forEach(r => {
          p1.visitors += r.visitors
          p1.pageviews += r.pageviews
        })
      }),

      // fetch stats for previous period
      request('/stats', {
        body: {
          start_date: toISO8601(previousStartDate),
          end_date: toISO8601(previousEndDate)
        }
      }).then(data => {
        data.forEach(r => {
          p2.visitors += r.visitors
          p2.pageviews += r.pageviews
        })
      })
    ]).then(() => {
      setTotals([p1, p2])
    })
  }

  useEffect(() => {
    const interval = setInterval(autoRefresh, 60000)
    return () => {
      clearInterval(interval)
    }
  }, [])

  useEffect(loadData, [startDate, endDate])
  let [p1, p2] = totals;
  let visitorsChange = p2.visitors > 0 ? p1.visitors / p2.visitors - 1 : null;
  let visitorsDiff = p1.visitors - p2.visitors;
  let pageviewsChange = p2.pageviews > 0 ? p1.pageviews / p2.pageviews - 1  : null;
  let pageviewsDiff = p1.pageviews - p2.pageviews;
  return (
    <div className='totals-container'>
      <div className='totals-box koko-fade'>
        <div className='totals-label'>{__('Total visitors', 'koko-analytics')}</div>
        <div className='totals-amount'>{formatLargeNumber(p1.visitors)} {p2.visitors > 0 ? <span
          className={visitorsChange > 0 ? 'up' : visitorsChange === 0 ? 'neutral' : 'down'}
        >{formatPercentage(visitorsChange)}
          </span> : ''}
        </div>
        <div className='totals-compare'>
          <span>{formatLargeNumber(Math.abs(visitorsDiff))} {visitorsDiff > 0 ? __('more than previous period', 'koko-analytics') : __('less than previous period', 'koko-analytics')}</span>
        </div>
      </div>
      <div className='totals-box koko-fade'>
        <div className='totals-label'>{__('Total pageviews', 'koko-analytics')}</div>
        <div className='totals-amount'>{formatLargeNumber(p1.pageviews)} {p2.pageviews > 0 ? <span
          className={pageviewsChange > 0 ? 'up' : pageviewsChange === 0 ? 'neutral' : 'down'}
        >{formatPercentage(pageviewsChange)}
          </span> : ''}
        </div>
        <div className='totals-compare'>
          <span>{formatLargeNumber(Math.abs(pageviewsDiff))} {pageviewsDiff > 0 ? __('more than previous period', 'koko-analytics') : __('less than previous period', 'koko-analytics')}</span>
        </div>
      </div>
      <Realtime />
    </div>
  )
}

