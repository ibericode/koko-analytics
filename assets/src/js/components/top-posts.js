import React, {useState, useEffect} from 'react'
import Pagination from './table-pagination.js'
import {request} from '../util/api'
import {toISO8601} from '../util/dates'
import { __ } from '@wordpress/i18n'

export default function TopPosts({ startDate, endDate }) {
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
  }, [startDate, endDate])

  // reload data when property for fetching change
  useEffect(loadData, [startDate, endDate, offset])

  function loadData() {
    request('/posts', {
      body: {
        start_date: toISO8601(startDate),
        end_date: toISO8601(endDate),
        offset,
        limit,
      }
    }).then(setItems)
  }

  return (
    <div className='box koko-fade top-posts'>
      <div className='head box-grid'>
        <div className=''>
          <span className='muted'>#</span>
          {__('Pages', 'koko-analytics')}
          <Pagination offset={offset} limit={limit} total={items.length} onUpdate={setOffset} />
        </div>
        <div className='amount-col' title={__('A visitor represents the number of sessions during which a page was viewed one or more times.', 'koko-analytics')}>{__('Visitors', 'koko-analytics')}</div>
        <div className='amount-col' title={__('A pageview is defined as a view of a page on your site. If a user clicks reload after reaching the page, this is counted as an additional pageview. If a visitor navigates to a different page and then returns to the original page, a second pageview is recorded as well.', 'koko-analytics')}>{__('Pageviews', 'koko-analytics')}</div>
      </div>
      <div className='body'>
        {items.map((p, i) => (
          <div key={`k-${p.id}-${i}`} className='box-grid koko-fade'>
            <div>
              <span className='muted'>{offset + i + 1}</span>
              <a href={p.post_permalink}>{p.post_title || '(no title)'}</a>
            </div>
            <div className='amount-col'>{Math.max(1, p.visitors)}</div>
            <div className='amount-col'>{p.pageviews}</div>
          </div>
        ))}
        {items.length === 0 && (
          <div className='box-grid'>{__('There\'s nothing here, yet!', 'koko-analytics')}</div>)}
      </div>
    </div>
  )
}

