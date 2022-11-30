import { h, Component } from 'preact'
import PropTypes from 'prop-types'
import api from '../util/api.js'
import Pagination from './table-pagination'
import { __ } from '@wordpress/i18n'
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

export default class TopReferrers extends Component {
  constructor (props) {
    super(props)
    this.state = {
      offset: 0,
      limit: 25,
      items: []
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

  loadData (offset = this.state.offset) {
    api.request('/referrers', {
      body: {
        start_date: api.formatDate(this.props.startDate),
        end_date: api.formatDate(this.props.endDate),
        offset,
        limit: this.state.limit
      }
    }).then(items => {
      items = items.map(enhance)
      this.setState({ items, offset })
    })
  }

  render () {
    const { offset, limit, items } = this.state
    return (
      <div className='box koko-fade top-referrers'>
        <div className='box-grid head'>
          <div className=''>
            <span className='muted'>#</span>
            {__('Referrers', 'koko-analytics')}

            <Pagination offset={offset} limit={limit} total={items.length} onUpdate={this.loadData} />
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
}

TopReferrers.propTypes = {
  startDate: PropTypes.instanceOf(Date).isRequired,
  endDate: PropTypes.instanceOf(Date).isRequired
}
