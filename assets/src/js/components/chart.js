import { h, Component } from 'preact'
import PropTypes from 'prop-types'
import format from 'date-fns/format'
import api from '../util/api.js'
import '../../sass/chart.scss'
import numbers from '../util/numbers'
import { modify } from '../util/colors'
import { isLastDayOfMonth } from '../util/dates.js'
import { __ } from '@wordpress/i18n'

const color1 = window.koko_analytics.colors[window.koko_analytics.colors.length - 1]
const color2 = modify(color1, -20)

function yScale (yMax) {
  const max = numbers.nice(yMax)
  const nTicks = 2
  const step = Math.round(max / nTicks)
  const ticks = []
  for (let i = 0; i <= max; i += step) {
    ticks.push(i)
  }

  return {
    ticks,
    max
  }
}

export default class Chart extends Component {
  constructor (props) {
    super(props)

    this.state = {
      dataset: [],
      yMax: 0,
      groupByMonth: false
    }
    this.tooltip = this.createTooltip()
    this.showTooltip = this.showTooltip.bind(this)
    this.hideTooltip = this.hideTooltip.bind(this)
    this.updateChart = this.updateChart.bind(this)
    this.loadData = this.loadData.bind(this)
    this.autoRefresh = this.autoRefresh.bind(this)
  }

  componentDidMount () {
    document.body.appendChild(this.tooltip)
    document.addEventListener('click', this.hideTooltip)
    this.refreshInterval = window.setInterval(this.autoRefresh, 60000)
    this.updateChart()
  }

  componentWillUnmount () {
    document.removeEventListener('click', this.hideTooltip)
    window.clearInterval(this.refreshInterval)
  }

  componentDidUpdate (prevProps) {
    if (this.props.startDate.getTime() === prevProps.startDate.getTime() && this.props.endDate.getTime() === prevProps.endDate.getTime()) {
      return
    }

    this.updateChart()
  }

  autoRefresh () {
    const now = new Date()

    if (this.props.startDate < now && this.props.endDate > now) {
      this.loadData()
    }
  }

  updateChart () {
    this.tooltip.style.display = 'none'
    this.loadData()
  }

  loadData () {
    const { startDate, endDate } = this.props

    // fetch actual stats
    api.request('/stats', {
      body: {
        start_date: api.formatDate(startDate),
        end_date: api.formatDate(endDate)
      }
    }).then(data => {
      const map = {}
      let yMax = 0
      let key
      const groupByMonth = startDate.getDate() === 1 && isLastDayOfMonth(endDate) && (endDate.getMonth() - startDate.getMonth()) >= 2

      // generate empty data for each tick
      const d = new Date(+startDate)
      // eslint-disable-next-line no-unmodified-loop-condition
      while (d <= endDate) {
        key = api.formatDate(d)
        map[key] = {
          date: new Date(d.getTime()),
          pageviews: 0,
          visitors: 0
        }

        groupByMonth ? d.setMonth(d.getMonth() + 1) : d.setDate(d.getDate() + 1)
      }

      // replace tick data with values from response data
      for (let i = 0; i < data.length; i++) {
        key = data[i].date

        if (groupByMonth) {
          const d = new Date(key)
          d.setDate(1)
          key = api.formatDate(d)
        }

        if (typeof map[key] === 'undefined') {
          console.error('Unexpected date in response data', key)
          continue
        }

        map[key].pageviews += data[i].pageviews
        map[key].visitors += data[i].visitors

        if (map[key].pageviews > yMax) {
          yMax = map[key].pageviews
        }
      }

      // Set visitors to be at least 1 if there are pageviews
      // This may not actually be technically true but it is easier than explaining the nuances of the tracking mechanism.
      const dataset = Object.values(map).map((d) => {
        if (d.pageviews > 0) {
          d.visitors = Math.max(1, d.visitors)
        }

        return d
      })

      this.setState({ dataset, yMax, groupByMonth })
    }).catch(() => {
      // empty chart if request somehow failed
      this.setState({
        dataset: [],
        yMax: 0
      })
    })
  }

  createTooltip () {
    const el = document.createElement('div')
    el.className = 'chart-tooltip'
    el.style.display = 'none'
    return el
  }

  showTooltip (data, barWidth) {
    const el = this.tooltip
    const { groupByMonth } = this.state

    return (evt) => {
      el.innerHTML = `
      <div class="tooltip-inner">
        <div class="heading">${format(data.date, groupByMonth ? 'MMM yyyy' : 'MMM d, yyyy - EEEE')}</div>
        <div class="content">
          <div class="visitors" style="border-top-color: ${color2}">
            <div class="amount">${data.visitors}</div>
            <div>${__('Visitors', 'koko-analytics')}</div>
          </div>
          <div class="pageviews" style="border-top-color: ${color1}">
            <div class="amount">${data.pageviews}</div>
            <div>${__('Pageviews', 'koko-analytics')}</div>
          </div>
        </div>
      </div>
      <div class="tooltip-arrow"></div>`

      const styles = evt.currentTarget.getBoundingClientRect()
      el.style.display = 'block'
      el.style.left = (styles.left + window.scrollX - 0.5 * el.clientWidth + 0.5 * barWidth) + 'px'
      el.style.top = (styles.y + window.scrollY - el.clientHeight) + 'px'
    }
  }

  hideTooltip (evt) {
    if (evt.type === 'click' && typeof (evt.target.matches) === 'function' && evt.target.matches('.chart *, .tooltip *')) {
      return
    }

    this.tooltip.style.display = 'none'
  }

  render (props, state) {
    const { dataset, yMax, groupByMonth } = state
    const ticks = dataset.length

    // hide entire component if showing just a single tick
    if (ticks <= 1) {
      return null
    }

    const width = props.width
    const height = props.height || Math.max(240, Math.min(window.innerHeight / 3, window.innerWidth / 2, 360))
    const padding = {
      left: 48,
      bottom: 36,
      top: 24,
      right: 24
    }
    const isLargeScreen = window.innerWidth >= 1280
    const drawTick = ticks <= 90
    const innerWidth = width - padding.left - padding.right
    const innerHeight = height - padding.bottom - padding.top
    const tickWidth = Math.round(innerWidth / ticks)
    const barWidth = Math.round(0.9 * tickWidth)
    const barPadding = Math.round((tickWidth - barWidth) / 2)
    const innerBarWidth = Math.round(barWidth * 0.6)
    const innerBarPadding = Math.round((barWidth - innerBarWidth) / 2)
    const y = yScale(yMax)
    const heightModifier = innerHeight / y.max
    const getX = v => v * tickWidth
    const getY = v => y.max > 0 ? Math.round(innerHeight - (v * heightModifier)) : innerHeight
    return (
      <div className='box'>
        <div className='chart-container'>
          <svg className='chart' width='100%' height={height}>
            <g className='axes'>
              <g className='axes-y' transform={`translate(0, ${padding.top})`} text-anchor='end'>
                {y.ticks.map((v, i) => {
                  const y = getY(v)
                  return (
                    <g key={i}>
                      <line stroke='#EEE' x1={padding.left} x2={innerWidth + padding.left} y1={y} y2={y} />
                      <text fill='#757575' x={0.75 * padding.left} y={y} dy='0.33em'>{numbers.formatPretty(v)}</text>
                    </g>
                  )
                })}
              </g>
              <g className='axes-x' text-anchor='middle' transform={`translate(${padding.left}, ${padding.top + innerHeight})`}>
                {dataset.map((d, i) => {
                  let label = null
                  if (i === 0) {
                    label = format(d.date, groupByMonth ? 'MMM yyyy' : 'MMM d, yyyy')
                  } else if (i === (ticks - 1)) {
                    label = format(d.date, groupByMonth ? 'MMM yyyy' : 'MMM d')
                  } else if (isLargeScreen) {
                    // for large screens only
                    if (ticks <= 7 || d.date.getDate() === 1) {
                      label = format(d.date, groupByMonth ? 'MMM' : 'MMM d')
                    } else if (ticks <= 31 && i >= 3 && i < (ticks - 3) && d.date.getDay() === 0) {
                      label = format(d.date, groupByMonth ? 'MMM' : 'MMM d')
                    }
                  }

                  // draw nothing if showing lots of ticks & this not first or last tick
                  if (!drawTick && !label) {
                    return null
                  }

                  const x = getX(i) + 0.5 * tickWidth
                  return (
                    <g key={d.date.toDateString()}>
                      <line stroke='#DDD' x1={x} x2={x} y1='0' y2='6' />
                      {label && <text fill='#757575' x={x} y='10' dy='1em'>{label}</text>}
                    </g>
                  )
                })}
              </g>
            </g>
            <g className='bars' transform={`translate(${padding.left}, ${padding.top})`}>
              {y.max > 0 && dataset.map((d, i) => {
                // do not draw unnecessary elements
                if (d.pageviews === 0) {
                  return ''
                }

                const pageviewHeight = Math.round(d.pageviews * heightModifier)
                const visitorHeight = Math.round(d.visitors * heightModifier)
                const x = getX(i)
                const showTooltip = this.showTooltip(d, barWidth)

                return (<g
                  key={d.date.toDateString()}
                  onClick={showTooltip}
                  onMouseEnter={showTooltip}
                  onMouseLeave={this.hideTooltip}
                >
                  <rect
                    className='pageviews'
                    height={pageviewHeight}
                    width={barWidth}
                    x={x + barPadding}
                    y={getY(d.pageviews)}
                    fill={color1}
                  />
                  <rect
                    className='visitors'
                    height={visitorHeight}
                    width={innerBarWidth}
                    x={x + barPadding + innerBarPadding}
                    y={getY(d.visitors)}
                    fill={color2}
                  />
                </g>)
              })}
            </g>
          </svg>
        </div>
      </div>
    )
  }
}

Chart.propTypes = {
  startDate: PropTypes.instanceOf(Date).isRequired,
  endDate: PropTypes.instanceOf(Date).isRequired,
  width: PropTypes.number.isRequired,
  height: PropTypes.number
}
