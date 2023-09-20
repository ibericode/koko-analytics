import React, {useState, useEffect} from 'react'
import Pagination from './pagination.js'
import {request} from '../util/api'
import {toISO8601} from '../util/dates'
import { __ } from '@wordpress/i18n'

export default function TopPosts({ startDate, endDate }) {
  let [offset, setOffset] = useState(0)
  let [items, setItems] = useState([])
  const limit = 10;

  // reload data when property for fetching change
  useEffect(() => {
    request('/posts', {
      body: {
        start_date: toISO8601(startDate),
        end_date: toISO8601(endDate),
        offset,
        limit,
      }
    }).then(setItems)
  }, [startDate, endDate, offset])

  return (
    <div className='ka-topx ka-box ka-fade top-posts'>
      <div className='ka-topx--head ka-topx--row'>
        <div className='ka-topx--rank'>#</div>
        <div className=''>
          {__('Pages', 'koko-analytics')}
          <Pagination offset={offset} limit={limit} total={items.length} onUpdate={setOffset} />
        </div>
        <div className='ka-topx--amount' title={__('A visitor represents the number of sessions during which a page was viewed one or more times.', 'koko-analytics')}>{__('Visitors', 'koko-analytics')}</div>
        <div className='ka-topx--amount' title={__('A pageview is defined as a view of a page on your site. If a user clicks reload after reaching the page, this is counted as an additional pageview. If a visitor navigates to a different page and then returns to the original page, a second pageview is recorded as well.', 'koko-analytics')}>{__('Pageviews', 'koko-analytics')}</div>
      </div>
      <div className='ka-topx--body'>
        {items.map((p, i) => (
          <div key={`k-${p.id}-${i}`} className='ka-topx--row ka-fade'>
            <div className='ka-topx--rank'>{offset + i + 1}</div>
            <div className={'ka-topx--col'}>
              <a href={p.post_permalink}>{p.post_title || '(no title)'}</a>
            </div>
            <div className='ka-topx--amount'>{Math.max(1, p.visitors)}</div>
            <div className='ka-topx--amount'>{p.pageviews}</div>
          </div>
        ))}
        {items.length === 0 && (
          <div style={{padding: '6px 12px 0'}}>{__('There\'s nothing here, yet!', 'koko-analytics')}</div>)}
      </div>
    </div>
  )
}

