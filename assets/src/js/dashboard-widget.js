import Chart from './components/chart.js'
const el = document.getElementById('koko-analytics-dashboard-widget-mount')
const {data, startDate, endDate} = window.koko_analytics;

function maybeRender() {
  if (!el.clientWidth) {
    return;
  }

  Chart(el, data.chart, startDate, endDate, 0, 200);
}

el.parentElement.style.display = '';
requestAnimationFrame(maybeRender);

/* eslint no-undef: "off" */
if (jQuery) {
  jQuery(document).on('postbox-toggled', maybeRender)
}
