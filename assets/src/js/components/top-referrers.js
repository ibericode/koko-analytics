import React, {useState, useEffect} from 'react'
import {request} from '../util/api'
import {toISO8601} from '../util/dates'
import Pagination from './pagination'
import { __ } from '@wordpress/i18n'

const URL_REGEX = /^https?:\/\/(www\.)?(.+?)\/?$/
const limit = window.koko_analytics.items_per_page;
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

  // reload data when property for fetching change
  useEffect(() => {
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
  }, [startDate, endDate, offset])

  return (
    <div className='ka-topx ka-box ka-fade top-referrers'>
      <div className='ka-topx--head ka-topx--row'>
        <div className='ka-topx--rank'>#</div>
        <div>{__('Referrers', 'koko-analytics')}</div>
        <div className='ka-topx--amount'>{__('Visitors', 'koko-analytics')}</div>
        <div className='ka-topx--amount'>{__('Pageviews', 'koko-analytics')}</div>
      </div>
      <div className='ka-topx--body'>
        {items.map((p, i) => (
          <div key={p.id} className='ka-topx--row ka-fade'>
            <div className='ka-topx--rank'>{offset + i + 1}</div>
            <div className='ka-topx--col'>
              {p.url.length ? <a href={p.url}>{p.displayUrl}</a> : p.displayUrl}
            </div>
            <div className='ka-topx--amount'>{Math.max(p.visitors, 1)}</div>
            <div className='ka-topx--amount'>{p.pageviews}</div>
          </div>
        ))}
        {items.length === 0 && (
          <div style={{padding: '6px 12px 0'}}>{__('There\'s nothing here, yet!', 'koko-analytics')}</div>)}
          <Pagination offset={offset} limit={limit} total={items.length} onUpdate={setOffset} />
      </div>
    </div>
  )
}

