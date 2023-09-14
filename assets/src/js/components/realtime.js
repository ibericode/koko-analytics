import {Component} from 'react'
import numbers from '../util/numbers.js'
import api from '../util/api.js'
import { __ } from '@wordpress/i18n'

export default class Realtime extends Component {
  state = {
    pageviews: 0
  }

  constructor (props) {
    super(props)

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
      <div className='totals-box koko-fade' key={'realtime-pageviews'}>
        <div className='totals-label'>{__('Realtime pageviews', 'koko-analytics')}</div>
        <div className='totals-amount'>{numbers.formatPretty(pageviews)}
        </div>
        <div className='totals-compare'>
          <span>
            <span>{__('pageviews in the last hour', 'koko-analytics')}</span>
          </span>
        </div>
      </div>
    )
  }
}
