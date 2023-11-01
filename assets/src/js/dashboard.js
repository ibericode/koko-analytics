import './globals.js'
import Chart from './components/chart.js'
import Datepicker from './components/datepicker.js'
import Totals from './components/totals.js'
import { PostsComponent, ReferrersComponent } from './components/block-components.js'
import { parseISO8601, toISO8601 } from './util/dates.js'
let { startDate, endDate, data } = window.koko_analytics
startDate = parseISO8601(startDate)
endDate = parseISO8601(endDate)

const blockComponents = [
  PostsComponent(document.querySelector('#ka-top-posts'), data.posts, startDate, endDate),
  ReferrersComponent(document.querySelector('#ka-top-referrers'), data.referrers, startDate, endDate)
]
window.koko_analytics.registerDashboardComponent = function(c) {
  blockComponents.push(c)
}

const totals = Totals(document.querySelector('#ka-totals'));
const chart = Chart(document.querySelector('#ka-chart'), data.chart, startDate, endDate);
Datepicker(document.querySelector('.ka-datepicker'), (newStartDate, newEndDate) => {
  startDate = newStartDate;
  endDate = newEndDate;
  [totals, chart, ...blockComponents].forEach(f => f.update(startDate, endDate));

  let s = new URLSearchParams(window.location.search);
  s.set('start_date', toISO8601(startDate))
  s.set('end_date', toISO8601(endDate))
  history.replaceState(undefined, undefined, window.location.pathname + '?' + s)
});

// every 1m, update all components with fresh data
// if we're looking at a date range involving today
window.setInterval(() => {
  endDate.setHours(23, 59, 59)
  if (endDate < new Date()) {
    return;
  }

  [totals, chart, ...blockComponents].forEach(f => f.update(startDate, endDate));
}, 60000);
