import Chart from './components/chart.js'
import { parseISO8601 } from './util/dates'
const el = document.getElementById('koko-analytics-dashboard-widget-mount')
const {data, startDate, endDate} = window.koko_analytics;

function maybeRender() {
  if (!el.clientWidth) {
    return;
  }

  Chart(el, data.chart, parseISO8601(startDate), parseISO8601(endDate), 200);
}

window.jQuery(document).on('postbox-toggled', maybeRender)
el.parentElement.style.display = '';
window.requestAnimationFrame(maybeRender);
