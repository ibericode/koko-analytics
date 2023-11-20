import { eventListenersModule, attributesModule, init, h } from "snabbdom"
import { request } from '../util/api.js'
import { magnitude, formatLargeNumber } from '../util/numbers.js'
import { format, parseISO8601 } from '../util/dates.js'
const {i18n} = window.koko_analytics;
const patch = init([eventListenersModule, attributesModule])
const tooltip = createTooltip()

function createTooltip () {
  const el = document.createElement('div')
  el.style.display = 'none'
  el.className = 'ka-chart--tooltip'
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

function getMaxPageviews(dataset) {
  let max = 0;
  let pv;
  for (let i = 0; i < dataset.length; i++) {
    pv = dataset[i].pageviews
    if (pv > max) {
      max = pv;
    }
  }
  return max;
}

/**
 * @param {HTMLElement|VNode} root
 * @param {array} data
 * @param {Date} startDate
 * @param {Date} endDate
 * @param {number} page
 * @param {number?} height
 * @returns {{update: update}}
 */
export default function(root, data, startDate, endDate, page, height) {
  if (!height) {
    height = 280;
  }
  const width = root.clientWidth;
  root.parentElement.style.minHeight = `${height+4}px`
  let dateFormatOptions = (endDate - startDate) >= 86400000 * 364 ? {month: 'short', year: 'numeric'} : undefined

  if (data.length) {
    root = patch(root,  render(data))
  } else {
    update(startDate, endDate, page)
  }

  document.body.appendChild(tooltip)
  addEventListener('click', (evt) => {
    // return early if click was anywhere inside this component
    for (let el = evt.target; el !== null; el = el.parentElement) {
      if (el === root.elm) {
        return;
      }
    }

    hideTooltip()
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

      tooltip.style.display = 'block';

      const styles = evt.currentTarget.getBoundingClientRect()
      const left = (styles.left + window.scrollX - 0.5 * tooltip.clientWidth + 0.5 * barWidth) + 'px';
      const top = (styles.y + window.scrollY - tooltip.clientHeight) + 'px';
      tooltip.style.left = left;
      tooltip.style.top = top;
    }
  }

  /**
   * @param {string} startDate
   * @param {string} endDate
   * @param {string} page
   */
  function update(startDate, endDate, page) {
    const groupByMonth = (parseISO8601(endDate) - parseISO8601(startDate)) >= 86400000 * 364
    dateFormatOptions = groupByMonth ? {month: 'short', year: 'numeric'} : undefined

    request('/stats', {
      start_date: startDate,
      end_date: endDate,
      monthly: groupByMonth ? 1 : 0,
      page: page > 0 ? page : 0,
    }).then(data => {
      root = patch(root,  render(data))
    })
  }

  function r(n) {
    return Math.round(n * 100) / 100
  }

  /**
   * @param {array} dataset
   * @returns {VNode}
   */
  function render(dataset) {
    if (dataset.length <= 1) {
      return h('!')
    }

    const yMax = getMaxPageviews(dataset)
    const yMaxNice = magnitude(yMax);
    const yTicks = [0, yMaxNice / 2, yMaxNice];
    const drawTick = dataset.length <= 90;
    const paddingLeft = 4 + Math.max(5, String(formatLargeNumber(yMaxNice)).length) * 8;
    const paddingTop = 6;
    const paddingBottom = 24;
    const innerWidth = width - paddingLeft;
    const innerHeight = height - paddingBottom - paddingTop;
    const heightModifier = innerHeight / yMaxNice;
    const tickWidth = r(innerWidth / dataset.length);
    const barPadding = dataset.length * 7 < innerWidth ? 2 : 0;
    const barWidth = tickWidth - barPadding * 2

    const getX = v => r(v * tickWidth)
    const getY = yMaxNice <= 0 ? (() => innerHeight) : (v =>innerHeight - (v * heightModifier))

    return h('svg', {
      attrs: {
        'width': '100%',
        'height': height,
      }
    }, [

      // grid lines + y-axes
      h('g', [
        h('g', {
          attrs: {
            transform: `translate(0, ${paddingTop})`,
            'text-anchor': 'end',
          }
        }, yTicks.map(v => {
          const y = getY(v)
          return h('g', [
            h('line', {
              attrs: {
                stroke: '#eee',
                x1: paddingLeft,
                x2: innerWidth + paddingLeft,
                y1: y,
                y2: y,
              }
            }),
            h('text', {
              attrs: {
                y,
                fill: '#757575',
                x: r((0.9 * paddingLeft) - 4),
                dy: '0.33em'
              }
            }, formatLargeNumber(v))
          ])
        })),
        h('g', {
          attrs: {
            class: 'axes-x',
            'text-anchor': 'start',
            'transform': `translate(${paddingLeft}, ${paddingTop + innerHeight})`
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
                'text-anchor': i === 0 ? 'start' : 'end'
              }
            }, format(d.date, dateFormatOptions)) : '',
          ])
        }).filter(el => el !== null))
      ]),
      h('g', {
        attrs: {
          class: 'bars',
          transform: `translate(${paddingLeft}, ${paddingTop})`
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
