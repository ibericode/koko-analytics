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
    <div className='totals-box koko-fade' key={'realtime-pageviews'}>
      <div className='totals-label'>{__('Realtime pageviews', 'koko-analytics')}</div>
      <div className='totals-amount'>{formatLargeNumber(pageviews)}
      </div>
      <div className='totals-compare'>
          <span>
            <span>{__('pageviews in the last hour', 'koko-analytics')}</span>
          </span>
      </div>
    </div>
  )
}
