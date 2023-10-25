import './globals.js'
import Chart from './components/chart.js'
import Datepicker from './components/datepicker.js'
import Totals from './components/totals'
import { PostsComponent, ReferrersComponent } from './components/block-components'
import { parseISO8601, toISO8601 } from './util/dates'
let { startDate, endDate } = window.koko_analytics
startDate = parseISO8601(startDate)
endDate = parseISO8601(endDate)

const blockComponents = [
  PostsComponent(document.querySelector('#ka-top-posts')),
  ReferrersComponent(document.querySelector('#ka-top-referrers'))
]
window.koko_analytics.registerDashboardComponent = function(c) {
  blockComponents.push(c)
}

const totals = Totals(document.querySelector('#ka-totals'));
const chart = Chart(document.querySelector('#ka-chart'));
Datepicker(document.querySelector('.ka-datepicker'), ({startDate, endDate}) => {
  [totals, chart, ...blockComponents].forEach(f => f.update(startDate, endDate))

  let s = new URLSearchParams(window.location.search);
  s.set('start_date', toISO8601(startDate))
  s.set('end_date', toISO8601(endDate))
  history.replaceState(undefined, undefined, window.location.pathname + '?' + s)
});

document.addEventListener('DOMContentLoaded', () => {
  [totals, chart, ...blockComponents].forEach(f => f.update(startDate, endDate))
})

