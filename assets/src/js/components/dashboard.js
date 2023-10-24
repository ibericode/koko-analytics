import React, {createElement, useState, useEffect} from 'react'
import Chart from './chart.js'
import Datepicker from './datepicker.js'
import TopPosts from './top-posts.js'
import TopReferrers from './top-referrers.js'
import datePresets from '../util/date-presets.js'

import { parseISO8601, toISO8601 } from '../util/dates.js'
import Totals from './totals'
const { defaultDateRange } = window.koko_analytics
let blockComponents = [
  TopPosts, TopReferrers
]
window.koko_analytics.registerDashboardComponent = function(c) {
  blockComponents.push(c)
}

/**
 * @returns {{endDate: Date, startDate: Date}}
 */
function parseDatesFromUrl () {
  let params = new URLSearchParams(window.location.search);
  const startDate = parseISO8601(params.get('start_date'))
  const endDate = parseISO8601(params.get('end_date'))
  if (!startDate || !endDate) {
    return datePresets.find(p => p.key === defaultDateRange).dates()
  }

  startDate.setHours(0, 0, 0)
  endDate.setHours(23, 59, 59)
  return {
    startDate,
    endDate,
  }
}

export default function Dashboard() {
  const [dates, setDates] = useState(parseDatesFromUrl())
  let totals;

  useEffect(() => {
    Datepicker(document.querySelector('.ka-datepicker'), ({startDate, endDate}) => {
      setDates({startDate, endDate})
      totals.fetch(startDate, endDate)

      let s = new URLSearchParams(window.location.search);
      s.set('start_date', toISO8601(startDate))
      s.set('end_date', toISO8601(endDate))
      history.replaceState(undefined, undefined, window.location.pathname + '?' + s)
    });

    totals = Totals();
    totals.fetch(startDate, endDate)
  }, [])

  const {startDate, endDate} = dates
  return (
    <main>
        <Chart startDate={startDate} endDate={endDate} width={document.getElementById('koko-analytics-mount').clientWidth} />
        <div className='ka-dashboard-components'>
          {blockComponents.map((c, key) => createElement(c, {startDate, endDate, key}))}
        </div>
    </main>
  )
}
