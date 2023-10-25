import { request } from '../util/api'
import { toISO8601 } from '../util/dates'
import { formatLargeNumber, formatPercentage } from '../util/numbers'

/**
 * @param {HTMLElement} root
 * @returns {{update: update}}
 */
export default function(root) {

  /**
   * @param {HTMLElement} root
   * @param {number} amount
   * @param {number} change
   * @param {number} changeRel
   */
  function updateDom(root, amount, change, changeRel) {
    root.children[1].children[0].textContent = formatLargeNumber(amount)
    root.children[1].children[1].textContent = changeRel !== null ? formatPercentage(changeRel) : ''
    root.classList.toggle('ka-up', change > 0)
    root.classList.toggle('ka-down', change < 0)
    root.children[2].firstElementChild.textContent = formatLargeNumber(Math.abs(change))
  }

  /**
   * @param {Date} startDate
   * @param {Date} endDate
   */
  function update(startDate, endDate) {
    request('/totals', {
      body: {
        start_date: toISO8601(startDate),
        end_date: toISO8601(endDate)
      }}).then(data => {
        updateDom(root.children[0], data.visitors, data.visitors_change, data.visitors_change_rel)
        updateDom(root.children[1], data.pageviews, data.pageviews_change, data.pageviews_change_rel)
    })
  }

  function updateRealtime() {
    request('/realtime', {
      body: {
        since: '-1 hour'
      }
    }).then(data => {
      root.children[2].children[1].textContent = formatLargeNumber(data)
    })
  }

  window.setInterval(updateRealtime, 60000)
  return {update}
}
