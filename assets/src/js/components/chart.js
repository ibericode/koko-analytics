import React, {useState, useEffect, useMemo, useRef} from "react"
import { request } from '../util/api.js'
import '../../sass/chart.scss'
import { magnitude, formatLargeNumber } from '../util/numbers'
import { modify } from '../util/colors'
import { isLastDayOfMonth, format, toISO8601 } from '../util/dates.js'
import { __ } from '@wordpress/i18n'

const {colors, date_format} = window.koko_analytics;
const color1 = colors.pop()
const color2 = modify(color1, -20)
const padding = {
  left: 48,
  bottom: 36,
  top: 24,
  right: 24
}
let tooltip;


function yScale (yMax) {
  const max = magnitude(yMax)
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

function createTooltip () {
  const el = document.createElement('div')
  el.className = 'chart-tooltip'
  el.style.display = 'none'
  return el
}

export default function Chart({startDate, endDate, width, height}) {
  const [dataset, setDataset] = useState([])
  const root = useRef(null)
  let yMax = useMemo(() => {
    return dataset.reduce((prev, current) => (prev.pageviews > current.pageviews) ? prev : current, 0).pageviews
  }, [dataset]);
  let groupByMonth = 0;


  useEffect(() => {
    tooltip = createTooltip();
    document.body.appendChild(tooltip)
    document.addEventListener('click', hideTooltip)

    // refresh dataset every 60s
    const refreshInterval = setInterval(() => {
      const now = new Date()
      if (startDate < now && endDate > now) {
        loadData()
      }
    }, 60000)

    return () => {
      document.removeEventListener('click', hideTooltip)
      window.clearInterval(refreshInterval)
    }
  }, [])

  useEffect(updateChart, [startDate, endDate])

  function updateChart () {
    tooltip.style.display = 'none'
    loadData()
  }

  function loadData () {
    // fetch actual stats
    request('/stats', {
      body: {
        start_date: toISO8601(startDate),
        end_date: toISO8601(endDate)
      }
    }).then(data => {
      const map = {}
      let key
      groupByMonth = startDate.getDate() === 1 && isLastDayOfMonth(endDate) && (endDate.getMonth() - startDate.getMonth()) >= 2

      // generate empty data for each tick
      const d = new Date(+startDate)
      // eslint-disable-next-line no-unmodified-loop-condition
      while (d <= endDate) {
        key = groupByMonth ? toISO8601(d).substring(0, 7) : toISO8601(d)
        map[key] = {
          date: new Date(d.getTime()),
          pageviews: 0,
          visitors: 0
        }

        groupByMonth ? d.setMonth(d.getMonth() + 1) : d.setDate(d.getDate() + 1)
      }

      // replace tick data with values from response data
      for (let i = 0, tick; i < data.length; i++) {
        let {date, visitors, pageviews} = data[i]
        key = groupByMonth ? date.substring(0, 7) : date
        tick = map[key]
        if (typeof tick === 'undefined') {
          console.error('Unexpected date in response data', key)
          continue
        }

        tick.pageviews += pageviews

        // If data returned from server had data for this day, it means there were pageviews,
        // and if there were pageviews, there was at least 1 visitor.
        // The data may not always reflect this b/c the cookie may have been set just before midnight,
        // so here we default to always adding at least 1 visitor whenever there are pageviews for that day.
        tick.visitors += Math.max(1, visitors)
      }

      setDataset(Object.values(map))
    }).catch((e) => {
      console.error(e)

      // empty chart if request somehow failed
      setDataset([])
    })
  }

  function createShowTooltip (data, barWidth) {
    return (evt) => {
      tooltip.innerHTML = `
      <div class="tooltip-inner">
        <div class="heading">${format(data.date, date_format, { day: !groupByMonth })}</div>
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
      tooltip.style.display = 'block'
      tooltip.style.left = (styles.left + window.scrollX - 0.5 * tooltip.clientWidth + 0.5 * barWidth) + 'px'
      tooltip.style.top = (styles.y + window.scrollY - tooltip.clientHeight) + 'px'
    }
  }

  /**
   * @param {MouseEvent} evt
   */
  function hideTooltip (evt) {
    if (evt.type === 'click' && typeof evt.target.matches === 'function' && evt.target.matches('.chart *, .tooltip *')) {
      return
    }

    tooltip.style.display = 'none'
  }

  // hide entire component if showing just a single tick
  if (dataset.length <= 1) {
    return null
  }

  if (!height) {
    height = height ?? Math.max(240, Math.min(window.innerHeight / 3, window.innerWidth / 2, 360))
  }
  const isLargeScreen = root.current && root.current.clientWidth >= 1280
  const drawTick = dataset.length <= 90
  const innerWidth = width - padding.left - padding.right
  const innerHeight = height - padding.bottom - padding.top
  const tickWidth = Math.round(innerWidth / dataset.length)
  const barWidth = Math.round(0.9 * tickWidth)
  const barPadding = Math.round((tickWidth - barWidth) / 2)
  const innerBarWidth = Math.round(barWidth * 0.6)
  const innerBarPadding = Math.round((barWidth - innerBarWidth) / 2)
  const y = yScale(yMax)
  const heightModifier = innerHeight / y.max
  const getX = v => v * tickWidth
  const getY = v => y.max > 0 ? Math.round(innerHeight - (v * heightModifier)) : innerHeight
  return (
    <div className='box' ref={root}>
      <div className='chart-container'>
        <svg className='chart' width='100%' height={height}>
          <g className='axes'>
            <g className='axes-y' transform={`translate(0, ${padding.top})`} textAnchor='end'>
              {y.ticks.map((v, i) => {
                const y = getY(v)
                return (
                  <g key={i}>
                    <line stroke='#EEE' x1={padding.left} x2={innerWidth + padding.left} y1={y} y2={y} />
                    <text fill='#757575' x={0.75 * padding.left} y={y} dy='0.33em'>{formatLargeNumber(v)}</text>
                  </g>
                )
              })}
            </g>
            <g className='axes-x' textAnchor='middle' transform={`translate(${padding.left}, ${padding.top + innerHeight})`}>
              {dataset.map((d, i) => {
                let label = null
                if (i === 0) {
                  label = format(d.date,  date_format, { day: !groupByMonth })
                } else if (i === (dataset.length - 1)) {
                  label = format(d.date,  date_format, { day: !groupByMonth, year: false })
                } else if (isLargeScreen) {
                  // for large screens only
                  if (dataset.length <= 7 || d.date.getDate() === 1) {
                    label = format(d.date, date_format, { year: false, day: !groupByMonth})
                  } else if (dataset.length <= 31 && i >= 3 && i < (dataset.length - 3) && d.date.getDay() === 0) {
                    label = format(d.date, date_format, { year: false, day: !groupByMonth})
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
              const showTooltip = createShowTooltip(d, barWidth)

              return (<g
                key={d.date.toDateString()}
                onClick={showTooltip}
                onMouseEnter={showTooltip}
                onMouseLeave={hideTooltip}
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
