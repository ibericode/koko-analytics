import Chart from './components/chart'
const el = document.getElementById('koko-analytics-dashboard-widget-mount')
const now = new Date()
const startDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 14, 0, 0, 0)
const endDate = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59)

function maybeRender() {
  if (!el.clientWidth) {
    return;
  }

  let chart = Chart(el, 200);
  chart.update(startDate, endDate)
}

window.jQuery(document).on('postbox-toggled', maybeRender)
maybeRender();
