import React, {createElement, useState, useEffect} from 'react'
import Chart from './chart.js'
import Datepicker from './datepicker.js'
import Totals from './totals.js'
import TopPosts from './top-posts.js'
import TopReferrers from './top-referrers.js'
import Nav from './nav.js'
import datePresets from '../util/date-presets.js'
import { parseISO8601, toISO8601 } from '../util/dates.js'
import { __ } from '@wordpress/i18n'
const settings = window.koko_analytics.settings
let blockComponents = [
  TopPosts, TopReferrers
]
window.koko_analytics.registerDashboardComponent = function(c) {
  blockComponents.push(c)
}


/**
 *
 * @returns {*}
 */
function getDatesFromPreset() {
    return (datePresets.find(p => p.key === settings.default_view) || datePresets[0]).dates()
}

/**
 *
 * @param {string} str
 * @returns {{endDate: Date, startDate: Date}}
 */
function parseDatesFromUrlHash (str) {
  let params = new URLSearchParams(str);
  const startDate = parseISO8601(params.get('start_date'))
  const endDate = parseISO8601(params.get('end_date'))
  if (!startDate || !endDate) {
    return getDatesFromPreset()
  }

  startDate.setHours(0, 0, 0)
  endDate.setHours(23, 59, 59)
  return { startDate, endDate }
}

export default function Dashboard({ history }) {
  const [dates, setDates] = useState(parseDatesFromUrlHash(history.location.search))

  useEffect(() => {
    return history.listen(({location, action}) => {
      if (action === 'POP') {
        setDates(parseDatesFromUrlHash(location.search))
      }
    })
  })

  /**
   * @param {Date} startDate
   * @param {Date} endDate
   */
  function onDatepickerUpdate (startDate, endDate) {
    if (+startDate === +endDate) {
      return
    }

    setDates({startDate, endDate})
    history.push(`/?start_date=${toISO8601(startDate)}&end_date=${toISO8601(endDate)}`)
  }

  const {startDate, endDate} = dates
  return (
    <main>
      <div>
        <div className='grid'>
          <div className='four'>
            <Datepicker startDate={startDate} endDate={endDate} onUpdate={onDatepickerUpdate} />
          </div>
          <Nav history={history} />
        </div>
        <Totals startDate={startDate} endDate={endDate} />
        <Chart startDate={startDate} endDate={endDate} width={document.getElementById('koko-analytics-mount').clientWidth} />
        <div className='grid'>
          {blockComponents.map((c, key) => createElement(c, {startDate, endDate, key}))}
        </div>
        <div>
          <span className={'description right'}>{__('Tip: use the arrow keys to quickly cycle through date ranges.', 'koko-analytics')}</span>
        </div>
      </div>
    </main>
  )
}
