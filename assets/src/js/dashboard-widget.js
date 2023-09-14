import Chart from './components/chart'
const el = document.getElementById('koko-analytics-dashboard-widget-mount')
const now = new Date()
const startDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 14, 0, 0, 0)
const endDate = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59)
import {createRoot} from 'react-dom'

function maybeRender() {
  if (!el.clientWidth) {
    return;
  }

  const root = createRoot(el)
  root.render(<Chart startDate={startDate} endDate={endDate} height={200} width={el.clientWidth} />)
}
window.jQuery(document).on('postbox-toggled', maybeRender)
maybeRender();
