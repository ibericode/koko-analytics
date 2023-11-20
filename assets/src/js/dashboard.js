import './globals.js'
import Chart from './components/chart.js'
import Datepicker from './components/datepicker.js'
import Totals from './components/totals.js'
import { PostsComponent, ReferrersComponent } from './components/block-components.js'
import { parseISO8601, toISO8601 } from './util/dates.js'
let { startDate, endDate, data } = window.koko_analytics
let page = 0;
let pageFilterEl = document.querySelector('.ka-page-filter');

function applyPageFilter(pageId, pageTitle, pageHref) {
  page = String(pageId) === String(page) ? 0 : pageId;
  [totals, chart].forEach(f => f.update(startDate, endDate, page));
  let a = document.createElement('a');
  a.setAttribute('href', pageHref);
  a.textContent = pageTitle;
  pageFilterEl.children[1].replaceChildren(a);
  pageFilterEl.style.display = page > 0 ? 'block' : 'none';
  document.body.classList.toggle('page-filter-active', page > 0);
}

document.querySelector('.ka-page-filter--close').addEventListener('click', () => {
  applyPageFilter(0, '');
})

const blockComponents = [
  PostsComponent(document.querySelector('#ka-top-posts'), data.posts, startDate, endDate, applyPageFilter),
  ReferrersComponent(document.querySelector('#ka-top-referrers'), data.referrers, startDate, endDate)
]
const totals = Totals(document.querySelector('#ka-totals'));
const chart = Chart(document.querySelector('#ka-chart'), data.chart, startDate, endDate, page);

Datepicker(document.querySelector('.ka-datepicker'), (newStartDate, newEndDate) => {
  startDate = toISO8601(newStartDate);
  endDate = toISO8601(newEndDate);
  [totals, chart].forEach(f => f.update(startDate, endDate, page));
  blockComponents.forEach(f => f.update(startDate, endDate));
  let s = new URLSearchParams(location.search);
  s.set('start_date', startDate)
  s.set('end_date', endDate)
  history.replaceState(undefined, undefined, location.pathname + '?' + s)
});

function registerDashboardComponent(c) {
  blockComponents.push(c)
}

window.koko_analytics.registerDashboardComponent = registerDashboardComponent;

// every 1m, update all components with fresh data
// if we're looking at a date range involving today
setInterval(() => {
  if (parseISO8601(endDate).setHours(23, 59, 59) < new Date()) {
    return;
  }

  [totals, chart].forEach(f => f.update(startDate, endDate, page));
  blockComponents.forEach(f => f.update(startDate, endDate));
}, 60000);
