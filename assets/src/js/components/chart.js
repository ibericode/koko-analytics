'use strict'

import React from 'react'
import PropTypes from 'prop-types'
import { format } from 'date-fns'
import api from '../util/api.js'
import '../../sass/chart.scss'
import numbers from '../util/numbers'
const colors = window.koko_analytics.colors
const i18n = window.koko_analytics.i18n

function step (v, ticks) {
  let step = (v - (v % ticks)) / ticks
  if (step === 0) {
    return 0
  }

  let round = 1000000
  while (v < round * ticks) {
    round /= 10
  }
  step = Math.floor(step / round) * round
  return step
}

export default class Component extends React.PureComponent {
  constructor (props) {
    super(props)

    this.state = {
      dataset: [],
      yMax: 0
    }

    this.base = React.createRef()
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

  componentDidUpdate (prevProps, prevState, snapshot) {
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
    // hide tooltip
    this.tooltip.style.display = 'none'

    // fill chart with 0's
    this.dataset = {}
    for (let d = new Date(this.props.startDate.getTime()); d <= this.props.endDate; d.setDate(d.getDate() + 1)) {
      const key = format(d, 'yyyy-MM-dd')
      this.dataset[key] = {
        date: new Date(d.getTime()),
        pageviews: 0,
        visitors: 0
      }
    }

    this.setState({
      dataset: Object.values(this.dataset),
      yMax: 0
    })
    this.loadData()
  }

  loadData () {
    const dataset = this.dataset
    let yMax = 0

    // fetch actual stats
    api.request('/stats', {
      body: {
        start_date: format(this.props.startDate, 'yyyy-MM-dd'),
        end_date: format(this.props.endDate, 'yyyy-MM-dd')
      }
    }).then(data => {
      data.forEach(d => {
        if (typeof (dataset[d.date]) === 'undefined') {
          console.error('Unexpected date in response data', d.date)
          return
        }

        const pageviews = parseInt(d.pageviews)
        const visitors = parseInt(d.visitors)
        dataset[d.date].pageviews = pageviews
        dataset[d.date].visitors = visitors

        if (pageviews > yMax) {
          yMax = pageviews
        }
      })

      this.setState({
        dataset: Object.values(dataset),
        yMax
      })
    })
  }

  createTooltip () {
    const el = document.createElement('div')
    el.className = 'tooltip'
    el.style.display = 'none'
    return el
  }

  showTooltip (data, barWidth) {
    const el = this.tooltip

    return (evt) => {
      el.innerHTML = `
      <div class="tooltip-inner">
        <div class="heading">${format(data.date, 'MMM d, yyyy - EEEE')}</div>
        <div class="content">
          <div class="visitors">
            <div class="amount">${data.visitors}</div>
            <div>${i18n.Visitors}</div>
          </div>
          <div class="pageviews">
            <div class="amount">${data.pageviews}</div>
            <div>${i18n.Pageviews}</div>
          </div>
        </div>
      </div>
      <div class="tooltip-arrow"></div>`

      const styles = evt.currentTarget.getBoundingClientRect()
      el.style.display = 'block'
      el.style.left = (styles.left + window.scrollX - 0.5 * el.clientWidth + barWidth) + 'px'
      el.style.top = (styles.y + window.scrollY - el.clientHeight) + 'px'
    }
  }

  hideTooltip (evt) {
    if (evt.type === 'click' && typeof (evt.target.matches) === 'function' && evt.target.matches('.chart *, .tooltip *')) {
      return
    }

    this.tooltip.style.display = 'none'
  }

  render () {
    const { dataset, yMax } = this.state
    const width = this.base.current ? this.base.current.clientWidth : window.innerWidth
    const height = this.props.height || Math.max(240, Math.min(window.innerHeight / 3, window.innerWidth / 2, 360))
    const padding = {
      left: 36,
      bottom: 26,
      top: 6,
      right: 12
    }
    const innerWidth = width - padding.left - padding.right
    const innerHeight = height - padding.bottom - padding.top
    const ticks = dataset.length
    const tickWidth = innerWidth / ticks
    const barWidth = 0.9 * tickWidth
    const barPadding = (tickWidth - barWidth) / 2
    const innerBarWidth = barWidth * 0.6
    const innerBarPadding = (barWidth - innerBarWidth) / 2
    const getX = i => i * tickWidth
    const getY = v => yMax > 0 ? innerHeight - (v / yMax * innerHeight) : innerHeight
    const yStep = step(yMax, 3) || 1

    // hide entire component if showing just a single data point
    if (ticks <= 1) {
      return null
    }

    return (
      <div className='box'>
        <div className='chart-container'>
          <svg className='chart' ref={this.base} width='100%' height={height}>
            <g className='axes'>
              <g className='axes-y' transform={`translate(0, ${padding.top})`} textAnchor='end'>
                {[0, 1, 2, 3].map(v => {
                  const value = v * yStep
                  if (value > yMax) {
                    return
                  }

                  const y = getY(value)
                  return (
                    <g key={v}>
                      <line stroke='#EEE' x1={30} x2={width} y1={y} y2={y} />
                      <text fill='#757575' x={24} y={y} dy='0.33em'>{numbers.formatPretty(value)}</text>
                    </g>
                  )
                })}
              </g>
              <g className='axes-x' transform={`translate(${padding.left}, ${padding.top + innerHeight})`} textAnchor='middle'>
                {dataset.map((d, i) => {
                  // draw nothing if showing lots of ticks & this not first or last tick
                  const tick = ticks <= 90

                  let label = null
                  if (i === 0) {
                    label = format(d.date, 'MMM d, yyyy')
                  } else if (i === ticks - 1) {
                    label = format(d.date, 'MMM d')
                  } else if (window.innerWidth >= 1280) {
                    // for large screens only
                    if (d.date.getDate() === 1 || ticks <= 7) {
                      label = format(d.date, 'MMM d')
                    } else if (ticks <= 31 && i >= 3 && i < (ticks - 3) && d.date.getDay() === 0) {
                      label = format(d.date, 'MMM d')
                    }
                  }

                  if (!tick && !label) {
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
              {yMax > 0 && dataset.map((d, i) => {
                // do not draw unnecessary elements
                if (d.pageviews === 0) {
                  return
                }

                const pageviewHeight = d.pageviews / yMax * innerHeight
                const visitorHeight = d.visitors / yMax * innerHeight
                const x = getX(i)
                const showTooltip = this.showTooltip(d, barWidth)

                return (<g
                  key={d.date}
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
                    fill={colors[2]}
                  />
                  <rect
                    className='visitors'
                    height={visitorHeight}
                    width={innerBarWidth}
                    x={x + barPadding + innerBarPadding}
                    y={getY(d.visitors)}
                    fill={colors[3]}
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

Component.propTypes = {
  startDate: PropTypes.instanceOf(Date).isRequired,
  endDate: PropTypes.instanceOf(Date).isRequired,
  height: PropTypes.number
}
