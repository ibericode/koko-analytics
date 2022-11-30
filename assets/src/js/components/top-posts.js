import { h, Component } from 'preact'
import PropTypes from 'prop-types'
import Pagination from './table-pagination.js'
import api from '../util/api.js'
import { __ } from '@wordpress/i18n'

export default class TopPosts extends Component {
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
    api.request('/posts', {
      body: {
        start_date: api.formatDate(this.props.startDate),
        end_date: api.formatDate(this.props.endDate),
        offset,
        limit: this.state.limit
      }
    }).then(items => {
      this.setState({ items, offset })
    })
  }

  render () {
    const { offset, limit, items } = this.state
    return (
      <div className='box koko-fade top-posts'>
        <div className='head box-grid'>
          <div className=''>
            <span className='muted'>#</span>
            {__('Pages', 'koko-analytics')}

            <Pagination offset={offset} limit={limit} total={items.length} onUpdate={this.loadData} />
          </div>
          <div className='amount-col' title={__('A visitor represents the number of sessions during which a page was viewed one or more times.', 'koko-analytics')}>{__('Visitors', 'koko-analytics')}</div>
          <div className='amount-col' title={__('A pageview is defined as a view of a page on your site. If a user clicks reload after reaching the page, this is counted as an additional pageview. If a visitor navigates to a different page and then returns to the original page, a second pageview is recorded as well.', 'koko-analytics')}>{__('Pageviews', 'koko-analytics')}</div>
        </div>
        <div className='body'>
          {items.map((p, i) => (
            <div key={p.id} className='box-grid koko-fade'>
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
}

TopPosts.propTypes = {
  startDate: PropTypes.instanceOf(Date).isRequired,
  endDate: PropTypes.instanceOf(Date).isRequired
}
