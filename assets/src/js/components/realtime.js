'use strict'

import React from 'react'
import numbers from '../util/numbers.js'
import api from '../util/api.js'
const i18n = window.koko_analytics.i18n

export default class Realtime extends React.PureComponent {
  constructor (props) {
    super(props)
    this.state = {
      pageviews: 0
    }

    this.loadData = this.loadData.bind(this)
  }

  componentDidMount () {
    this.loadData()
    this.refreshInterval = window.setInterval(this.loadData, 60000)
  }

  componentWillUnmount () {
    window.clearInterval(this.refreshInterval)
  }

  loadData () {
    api.request('/realtime', {
      body: {
        since: '-1 hour'
      }
    }).then(pageviews => {
      this.setState({ pageviews })
    })
  }

  render () {
    const { pageviews } = this.state

    return (
      <div className='totals-box fade' key={'realtime-pageviews'}>
        <div className='totals-label'>{i18n['Realtime pageviews']}</div>
        <div className='totals-amount'>{numbers.formatPretty(pageviews)}
        </div>
        <div className='totals-compare'>
          <span>
            <span>{i18n['Pageviews in the last hour']}</span>
          </span>
        </div>
      </div>
    )
  }
}
