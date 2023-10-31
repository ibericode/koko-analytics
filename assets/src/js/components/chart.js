import { request } from '../util/api.js'
import { magnitude, formatLargeNumber } from '../util/numbers.js'
import { toISO8601, format } from '../util/dates.js'
import { eventListenersModule, attributesModule, init, h } from "snabbdom"
const {i18n} = window.koko_analytics;
const patch = init([eventListenersModule, attributesModule])
const padding = {
  left: 48,
  bottom: 36,
  top: 24,
  right: 24
}
const tooltip = createTooltip()

/**
 *
 * @param {number} yMax
 * @returns {{ticks: *[], max: number}}
 */
function yScale (yMax) {
  const max = magnitude(yMax)
  return {
    ticks: [0, max / 2, max],
    max
  }
}

function createTooltip () {
  const el = document.createElement('div')
  el.className = 'ka-chart--tooltip'
  el.style.display = 'none'
  el.innerHTML = `
<div class="ka-chart--tooltip-box">
  <div class="ka-chart--tooltip-heading"></div>
  <div style="display: flex">
    <div class="ka-chart--tooltip-content ka--visitors">
      <div class="ka-chart--tooltip-amount"></div>
      <div>${i18n['Visitors']}</div>
    </div>
    <div class="ka-chart--tooltip-content ka--pageviews">
      <div class="ka-chart--tooltip-amount"></div>
      <div>${i18n['Pageviews']}</div>
    </div>
  </div>
</div>
<div class="ka-chart--tooltip-arrow"></div>`

  return el
}

function hideTooltip() {
  tooltip.style.display = 'none'
}

/**
 * @param {HTMLElement|VNode} root
 * @param {array} data
 * @param {Date} startDate
 * @param {Date} endDate
 * @param {number} height
 * @returns {{update: update}}
 */
export default function(root, data, startDate, endDate, height) {
  if (!height) {
    height = Math.max(240, Math.min(window.innerHeight / 3,   360));
  }
  root.parentElement.style.minHeight = `${height+4}px`
  let dateFormatOptions = (endDate - startDate) >= 86400000 * 364 ? {month: 'short', year: 'numeric'} : undefined
  let width = root.clientWidth
  const innerWidth = width - padding.left - padding.right
  const innerHeight = height - padding.bottom - padding.top

  if (data.length) {
    root = patch(root,  render(data))
  } else {
    update(startDate, endDate)
  }

  document.body.appendChild(tooltip)
  document.addEventListener('click', (evt) => {
    // don't hide if click was inside tooltip
    if (evt.target.matches('.ka-chart *,.ka-chart--tooltip *')) {
      return
    }

    tooltip.style.display = 'none'
  })

  /**
   * @param {{pageviews: number, visitors: number, date: Date}} data
   * @param {number} barWidth
   * @returns {(function(*): void)|*}
   */
  function createShowTooltip (data, barWidth) {
    return (evt) => {
      tooltip.querySelector('.ka-chart--tooltip-heading').textContent = format(data.date, dateFormatOptions);
      tooltip.querySelector('.ka--visitors').children[0].textContent = data.visitors;
      tooltip.querySelector('.ka--pageviews').children[0].textContent = data.pageviews;

      const styles = evt.currentTarget.getBoundingClientRect()
      tooltip.style.display = 'block'
      tooltip.style.left = (styles.left + window.scrollX - 0.5 * tooltip.clientWidth + 0.5 * barWidth) + 'px'
      tooltip.style.top = (styles.y + window.scrollY - tooltip.clientHeight) + 'px'
    }
  }

  /**
   * @param {Date} startDate
   * @param {Date} endDate
   */
  function update(startDate, endDate) {
    const groupByMonth = (endDate - startDate) >= 86400000 * 364
    dateFormatOptions = groupByMonth ? {month: 'short', year: 'numeric'} : undefined

    request('/stats', {
      start_date: toISO8601(startDate),
      end_date: toISO8601(endDate),
      monthly: groupByMonth ? 1 : 0,
    }).then(data => {
      root = patch(root,  render(data))
    })
  }

  /**
   * @param {array} dataset
   * @returns {VNode}
   */
  function render(dataset) {
    if (dataset.length <= 1) {
      return h('!')
    }

    const
      tickWidth = innerWidth / dataset.length,
      barWidth = 0.9 * tickWidth,
      barPadding = (tickWidth - barWidth) / 2
    const yMax = dataset.reduce((prev, current) => (prev.pageviews > current.pageviews) ? prev : current, 0).pageviews
    const y = yScale(yMax)
    const drawTick = dataset.length <= 90
    const heightModifier = innerHeight / y.max
    const getX = v => v * tickWidth
    const getY = y.max <= 0 ? (() => innerHeight) : (v =>innerHeight - (v * heightModifier))

    return h('svg', {
      attrs: {
        'width': '100%',
        'height': height,
      }
    }, [
      h('g', [
        h('g', {
          attrs: {
            class: 'axes-y',
            transform: `translate(0, ${padding.top})`,
            'text-anchor': 'end',
          }
        }, y.ticks.map(v => {
          const y = getY(v)
          return h('g', [
            h('line', {
              attrs: {
                stroke: '#eee',
                x1: padding.left,
                x2: innerWidth + padding.left,
                y1: y,
                y2: y,
              }
            }),
            h('text', {
              attrs: {
                y,
                fill: '#757575',
                x: 0.75 * padding.left,
                dy: '0.33em'
              }
            }, formatLargeNumber(v))
          ])
        })),
        h('g', {
          attrs: {
            class: 'axes-x',
            'text-anchor': 'middle',
            'transform': `translate(${padding.left}, ${padding.top + innerHeight})`
          }
        }, dataset.map((d, i) => {
          let label = i === 0 || i === dataset.length - 1 ? d.date : null

          // draw nothing if showing lots of ticks & this not first or last tick
          if (!drawTick && !label) {
            return null
          }

          const x = getX(i) + 0.5 * tickWidth
          return h('g', [
            h('line', {
              attrs: {
                stroke: '#ddd',
                x1: x,
                x2: x,
                y1: 0,
                y2: 6,
              }
            }),
            label ? h('text', {
              attrs: {
                fill: '#757575',
                x: x,
                y: 10,
                dy: '1em',
              }
            }, format(d.date, dateFormatOptions)) : '',
          ])
        }).filter(el => el !== null))
      ]),
      h('g', {
        attrs: {
          class: 'bars',
          transform: `translate(${padding.left}, ${padding.top})`
        }
      }, dataset.map((d, i) => {
        const pageviewHeight = d.pageviews * heightModifier
        const visitorHeight = d.visitors * heightModifier
        const x = getX(i)
        const showTooltip = createShowTooltip(d, barWidth)

        return h('g', {
          on: {
            click: showTooltip,
            mouseenter: showTooltip,
            mouseleave: hideTooltip,
          }
        }, [
          h('rect', {
            attrs: {
              class: 'ka--pageviews',
              height: pageviewHeight,
              width: barWidth,
              x: x + barPadding,
              y: getY(d.pageviews)
            }
          }),
          h('rect', {
            attrs: {
              class: 'ka--visitors',
              height: visitorHeight,
              width: barWidth,
              x: x + barPadding,
              y: getY(d.visitors)
            }
          }),
        ])
      }))
    ])
  }

  return {update}
}
