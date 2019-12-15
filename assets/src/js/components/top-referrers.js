'use strict'

import React from 'react'
import { format } from 'date-fns'
import api from '../util/api.js'
import Pagination from './table-pagination'
const i18n = window.koko_analytics.i18n
const URL_REGEX = /^https?:\/\/(www\.)?(.+?)\/?$/

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

export default class TopReferrers extends React.PureComponent {
  constructor (props) {
    super(props)
    this.state = {
      offset: 0,
      limit: 10,
      items: []
    }
    this.fetch = this.fetch.bind(this)
  }

  componentDidUpdate (prevProps, prevState, snapshot) {
    if (this.props.startDate.getTime() === prevProps.startDate.getTime() && this.props.endDate.getTime() === prevProps.endDate.getTime()) {
      return
    }

    this.fetch()
  }

  fetch (offset = this.state.offset) {
    api.request('/referrers', {
      body: {
        start_date: format(this.props.startDate, 'yyyy-MM-dd'),
        end_date: format(this.props.endDate, 'yyyy-MM-dd'),
        offset: offset,
        limit: this.state.limit
      }
    }).then(items => {
        	items = items.map(enhance)
          	this.setState({ items, offset })
    })
  }

  componentDidMount () {
    this.fetch()
  }

  render () {
    const { offset, limit, items } = this.state
    return (
      <div className='box fade top-referrers'>
        <div className='box-grid head'>
          <div className=''>
            <span className='muted'>#</span>
            {i18n.Referrers}

            <Pagination offset={offset} limit={limit} total={items.length} onUpdate={this.fetch} />
          </div>
          <div className='amount-col'>{i18n.Visitors}</div>
          <div className='amount-col'>{i18n.Pageviews}</div>
        </div>
        <div className='body'>
          {items.map((p, i) => (
            <div key={p.id} className='box-grid fade'>
              <div className='url-col'>
                <span className='muted'>{offset + i + 1}</span>
                {p.url.length ? <a href={p.url}>{p.displayUrl}</a> : p.displayUrl}
              </div>
              <div className='amount-col'>{Math.max(p.visitors, 1)}</div>
              <div className='amount-col'>{p.pageviews}</div>
            </div>
          ))}
          {items.length === 0 && (
            <div className='box-grid'>{i18n['There\'s nothing here, yet!']}</div>)}
        </div>
      </div>
    )
  }
}
