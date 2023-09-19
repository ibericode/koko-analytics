import React, {useState, useEffect} from 'react'
import { formatLargeNumber } from '../util/numbers.js'
import {request} from '../util/api'
import { __ } from '@wordpress/i18n'

export default function Realtime() {
  const [pageviews, setPageviews] = useState(0)

  function load() {
    request('/realtime', {
      body: {
        since: '-1 hour'
      }
    }).then(setPageviews)
  }

  useEffect(() => {
    load()
    const refreshInterval = setInterval(load, 60000)
    return () => {
      clearInterval(refreshInterval)
    }
  }, [])

  return (
    <div className='ka-fade' key={'realtime-pageviews'}>
      <div className='ka-totals--label'>{__('Realtime pageviews', 'koko-analytics')}</div>
      <div className='ka-totals--amount'>{formatLargeNumber(pageviews)}</div>
      <div className='ka-totals--subtext'>
          {__('pageviews in the last hour', 'koko-analytics')}
      </div>
    </div>
  )
}
