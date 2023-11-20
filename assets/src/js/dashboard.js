import './globals.js'
import Chart from './components/chart.js'
import Datepicker from './components/datepicker.js'
import Totals from './components/totals.js'
import { PostsComponent, ReferrersComponent } from './components/block-components.js'
import { parseISO8601, toISO8601 } from './util/dates.js'
let { startDate, endDate, data } = window.koko_analytics
let state = {
  startDate: parseISO8601(startDate),
  endDate: parseISO8601(endDate),
  page: 0,
};
const blockComponents = [
  PostsComponent(document.querySelector('#ka-top-posts'), data.posts, state),
  ReferrersComponent(document.querySelector('#ka-top-referrers'), data.referrers, state)
]
const totals = Totals(document.querySelector('#ka-totals'), state);
const chart = Chart(document.querySelector('#ka-chart'), data.chart, state);

Datepicker(document.querySelector('.ka-datepicker'), (newStartDate, newEndDate) => {
  updateState({ startDate: newStartDate, endDate: newEndDate });
  [totals, chart, ...blockComponents].forEach(f => f.update());
    let s = new URLSearchParams(location.search);
    s.set('start_date', toISO8601(state.startDate))
    s.set('end_date', toISO8601(state.endDate))
    history.replaceState(undefined, undefined, location.pathname + '?' + s)
});

function registerDashboardComponent(c) {
  blockComponents.push(c)
}

// TODO: Rename this, or make it non-global
function updateState(newState) {
  state = Object.assign(state, newState);
  [totals, chart].forEach(f => f.update());
}

window.koko_analytics.registerDashboardComponent = registerDashboardComponent;
window.koko_analytics.updateState = updateState;

// every 1m, update all components with fresh data
// if we're looking at a date range involving today
window.setInterval(() => {
  if (state.endDate.setHours(23, 59, 59) < new Date()) {
    return;
  }

  [totals, chart, ...blockComponents].forEach(f => f.update());
}, 60000);
