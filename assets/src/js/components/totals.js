import { h, Component } from 'preact'
import PropTypes from 'prop-types'
import '../../sass/totals.scss'
import numbers from '../util/numbers.js'
import api from '../util/api.js'
import Realtime from './realtime.js'
import { __ } from '@wordpress/i18n'

export default class Totals extends Component {
  constructor (props) {
    super(props)
    this.state = {
      visitors: 0,
      visitorsChange: 0,
      visitorsDiff: 0,
      visitorsPrevious: 0,
      pageviews: 0,
      pageviewsChange: 0,
      pageviewsDiff: 0,
      pageviewsPrevious: 0,
      hash: 'initial' // recompute this when data changes so we can redraw elements for animations
    }

    this.loadData = this.loadData.bind(this)
    this.autoRefresh = this.autoRefresh.bind(this)
  }

  componentDidMount () {
    this.loadData()
    this.refreshInterval = window.setInterval(this.autoRefresh, 60000)
  }

  componentWillUnmount () {
    window.clearInterval(this.refreshInterval)
  }

  componentDidUpdate (prevProps) {
    if (this.props.startDate.getTime() === prevProps.startDate.getTime() && this.props.endDate.getTime() === prevProps.endDate.getTime()) {
      return
    }

    this.loadData()
  }

  autoRefresh () {
    const now = new Date()
    if (this.props.startDate < now && this.props.endDate > now) {
      this.loadData()
    }
  }

  loadData () {
    const { startDate, endDate } = this.props
    const diff = (endDate.getTime() - startDate.getTime()) + 1
    const previousStartDate = new Date(startDate.getTime() - diff)
    const previousEndDate = new Date(endDate.getTime() - diff)
    let visitors = 0
    let pageviews = 0
    let visitorsChange = 0
    let pageviewsChange = 0
    let visitorsDiff = 0
    let pageviewsDiff = 0
    let visitorsPrevious = 0
    let pageviewsPrevious = 0

    Promise.all([
      // fetch stats for current period
      api.request('/stats', {
        body: {
          start_date: api.formatDate(this.props.startDate),
          end_date: api.formatDate(this.props.endDate)
        }
      }).then(data => {
        data.forEach(r => {
          visitors += r.visitors
          pageviews += r.pageviews
        })
      }),

      // fetch stats for previous period
      api.request('/stats', {
        body: {
          start_date: api.formatDate(previousStartDate),
          end_date: api.formatDate(previousEndDate)
        }
      }).then(data => {
        data.forEach(r => {
          visitorsPrevious += r.visitors
          pageviewsPrevious += r.pageviews
        })
      })
    ]).then(() => {
      // show a minimum of 1 visitors whenever we have pageviews
      if (visitors === 0 && pageviews > 0) {
        visitors = 1
      }
      if (visitorsPrevious === 0 && pageviewsPrevious > 0) {
        visitorsPrevious = 1
      }

      if (visitorsPrevious > 0) {
        visitorsDiff = visitors - visitorsPrevious
        visitorsChange = Math.round((visitors / visitorsPrevious - 1) * 100)
      }

      if (pageviewsPrevious > 0) {
        pageviewsDiff = pageviews - pageviewsPrevious
        pageviewsChange = Math.round((pageviews / pageviewsPrevious - 1) * 100)
      }

      const hash = api.formatDate(this.props.startDate) + '-' + api.formatDate(this.props.endDate)

      this.setState({ visitors, visitorsPrevious, visitorsDiff, visitorsChange, pageviews, pageviewsPrevious, pageviewsDiff, pageviewsChange, hash })
    })
  }

  render () {
    const { visitors, visitorsDiff, visitorsChange, pageviews, pageviewsDiff, pageviewsChange, hash } = this.state

    return (
      <div className='totals-container'>
        <div className='totals-box koko-fade' key={hash + '-visitors'}>
          <div className='totals-label'>{__('Total visitors', 'koko-analytics')}</div>
          <div className='totals-amount'>{numbers.formatPretty(visitors)} <span
            className={visitorsChange > 0 ? 'up' : visitorsChange === 0 ? 'neutral' : 'down'}
          >{numbers.formatPercentage(visitorsChange)}
          </span>
          </div>
          <div className='totals-compare'>
            <span>{numbers.formatPretty(Math.abs(visitorsDiff))} {visitorsDiff > 0 ? __('more than previous period', 'koko-analytics') : __('less than previous period', 'koko-analytics')}</span>
          </div>
        </div>
        <div className='totals-box koko-fade' key={hash + '-pageviews'}>
          <div className='totals-label'>{__('Total pageviews', 'koko-analytics')}</div>
          <div className='totals-amount'>{numbers.formatPretty(pageviews)} <span
            className={pageviewsChange > 0 ? 'up' : pageviewsChange === 0 ? 'neutral' : 'down'}
          >{numbers.formatPercentage(pageviewsChange)}
          </span>
          </div>
          <div className='totals-compare'>
            <span>{numbers.formatPretty(Math.abs(pageviewsDiff))} {pageviewsDiff > 0 ? __('more than previous period', 'koko-analytics') : __('less than previous period', 'koko-analytics')}</span>
          </div>
        </div>
        <Realtime />
      </div>
    )
  }
}

Totals.propTypes = {
  startDate: PropTypes.instanceOf(Date).isRequired,
  endDate: PropTypes.instanceOf(Date).isRequired
}
