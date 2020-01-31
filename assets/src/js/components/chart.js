'use strict'

import React from 'react'
import PropTypes from 'prop-types'
import format from 'date-fns/format'
import api from '../util/api.js'
import '../../sass/chart.scss'
import numbers from '../util/numbers'
import { modify } from './../util/colors.js'
const i18n = window.koko_analytics.i18n
const color1 = window.koko_analytics.colors[3]
const color2 = modify(color1, 30)

function yScale (_min, _max, maxTicks) {
  function niceNum (range, round) {
    const exponent = Math.floor(Math.log10(range))
    const fraction = range / Math.pow(10, exponent)
    let niceFraction

    if (round) {
      if (fraction < 1.5) niceFraction = 1
      else if (fraction < 3) niceFraction = 2
      else if (fraction < 7) niceFraction = 5
      else niceFraction = 10
    } else {
      if (fraction <= 1) niceFraction = 1
      else if (fraction <= 2) niceFraction = 2
      else if (fraction <= 5) niceFraction = 5
      else niceFraction = 10
    }

    return niceFraction * Math.pow(10, exponent)
  }

  const range = niceNum(_max - _min, false)
  const step = niceNum(range / (maxTicks - 1), true)
  const max = Math.ceil(_max / step) * step

  const ticks = []
  for (let i = _min; i <= max; i = i + step) {
    ticks.push(i)
  }

  return {
    ticks,
    max
  }
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
      const key = api.formatDate(d)
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
        start_date: api.formatDate(this.props.startDate),
        end_date: api.formatDate(this.props.endDate)
      }
    }).then(data => {
      data.forEach(d => {
        // Note: date in response data should match format from Date.toISOString() from above
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
          <div class="visitors" style="border-top-color: ${color2}">
            <div class="amount">${data.visitors}</div>
            <div>${i18n.Visitors}</div>
          </div>
          <div class="pageviews" style="border-top-color: ${color1}">
            <div class="amount">${data.pageviews}</div>
            <div>${i18n.Pageviews}</div>
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
    const y = yScale(0, yMax, 4)
    const getX = i => i * tickWidth
    const getY = v => y.max > 0 ? innerHeight - (v / y.max * innerHeight) : innerHeight

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
                {y.ticks.map((v, i) => {
                  const y = getY(v)
                  return (
                    <g key={i}>
                      <line stroke='#EEE' x1={30} x2={width} y1={y} y2={y} />
                      <text fill='#757575' x={24} y={y} dy='0.33em'>{numbers.formatPretty(v)}</text>
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
              {y.max > 0 && dataset.map((d, i) => {
                // do not draw unnecessary elements
                if (d.pageviews === 0) {
                  return
                }

                const pageviewHeight = d.pageviews / y.max * innerHeight
                const visitorHeight = d.visitors / y.max * innerHeight
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

Component.propTypes = {
  startDate: PropTypes.instanceOf(Date).isRequired,
  endDate: PropTypes.instanceOf(Date).isRequired,
  height: PropTypes.number
}
