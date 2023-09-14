import React, {useState, useEffect} from 'react'
import {request} from '../util/api'
import {toISO8601} from '../util/dates'
import Pagination from './table-pagination'
import { __ } from '@wordpress/i18n'
const URL_REGEX = /^https?:\/\/(www\.)?(.+?)\/?$/

/**
 * @param {string} url
 * @returns {string}
 */
function formatUrl (url) {
  return url.replace(URL_REGEX, '$2')
}


function enhance (item) {
  item.displayUrl = formatUrl(item.url)

  if (item.url.indexOf('https://t.co/') === 0) {
    item.url = 'https://twitter.com/search?q=' + encodeURI(item.url)
  } else if (item.url.indexOf('android-app://') === 0) {
    item.displayUrl = item.url.replace('android-app://', 'Android app: ')
    item.url = item.url.replace('android-app://', 'https://play.google.com/store/apps/details?id=')
  }

  return item
}

export default function TopReferrers({ startDate, endDate }) {
  let [offset, setOffset] = useState(0)
  let [items, setItems] = useState([])
  const limit = 25;

  // periodically reload data
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
  }, [])

  // reload data when property for fetching change
  useEffect(loadData, [startDate, endDate, offset])

  function loadData() {
    request('/referrers', {
      body: {
        start_date: toISO8601(startDate),
        end_date: toISO8601(endDate),
        offset,
        limit,
      }
    }).then(items => {
      items = items.map(enhance)
      setItems(items)
    })
  }

  return (
    <div className='box koko-fade top-referrers'>
      <div className='box-grid head'>
        <div className=''>
          <span className='muted'>#</span>
          {__('Referrers', 'koko-analytics')}
          <Pagination offset={offset} limit={limit} total={items.length} onUpdate={setOffset} />
        </div>
        <div className='amount-col'>{__('Visitors', 'koko-analytics')}</div>
        <div className='amount-col'>{__('Pageviews', 'koko-analytics')}</div>
      </div>
      <div className='body'>
        {items.map((p, i) => (
          <div key={p.id} className='box-grid koko-fade'>
            <div className='url-col'>
              <span className='muted'>{offset + i + 1}</span>
              {p.url.length ? <a href={p.url}>{p.displayUrl}</a> : p.displayUrl}
            </div>
            <div className='amount-col'>{Math.max(p.visitors, 1)}</div>
            <div className='amount-col'>{p.pageviews}</div>
          </div>
        ))}
        {items.length === 0 && (
          <div className='box-grid'>{__('There\'s nothing here, yet!', 'koko-analytics')}</div>)}
      </div>
    </div>
  )
}

