import './globals.js'
import Chart from './components/chart.js'
import Datepicker from './components/datepicker.js'
import Totals from './components/totals'
import datePresets from './util/date-presets.js'
import { parseISO8601, toISO8601 } from './util/dates.js'
import { PostsComponent, ReferrersComponent } from './components/block-components'
const { defaultDateRange } = window.koko_analytics

let blockComponents = []
window.koko_analytics.registerDashboardComponent = function(c) {
  blockComponents.push(c)
}
/**
 * @returns {{endDate: Date, startDate: Date}}
 */
function parseDatesFromUrl () {
  let params = new URLSearchParams(window.location.search);
  const startDate = parseISO8601(params.get('start_date'))
  const endDate = parseISO8601(params.get('end_date'))
  if (!startDate || !endDate) {
    return datePresets.find(p => p.key === defaultDateRange).dates()
  }

  return {
    startDate,
    endDate,
  }
}

let chart, totals, topPosts, topReferrers;
let {startDate, endDate} = parseDatesFromUrl()

Datepicker(document.querySelector('.ka-datepicker'), ({startDate, endDate}) => {
  [totals, chart, topPosts, topReferrers].forEach(f => f.update(startDate, endDate))

  let s = new URLSearchParams(window.location.search);
  s.set('start_date', toISO8601(startDate))
  s.set('end_date', toISO8601(endDate))
  history.replaceState(undefined, undefined, window.location.pathname + '?' + s)
});
totals = Totals(document.querySelector('#ka-totals'));
chart = Chart(document.querySelector('#ka-chart'));
topPosts = PostsComponent(document.querySelector('#ka-top-posts'));
topReferrers = ReferrersComponent(document.querySelector('#ka-top-referrers'));
[totals, chart, topPosts, topReferrers].forEach(f => f.update(startDate, endDate))

