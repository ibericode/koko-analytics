// fill chart
import { Chart } from './imports/chart.js';
import './imports/draggable-components.js';
import './imports/auto-reload.js';
import './imports/datepicker.js';

document.addEventListener('DOMContentLoaded', () => {
  new Chart(document.getElementById('ka-chart'));
});

// save scroll position when navigating away
function storeScrollPosition() {
    sessionStorage.setItem("scrollX", window.pageXOffset);
    sessionStorage.setItem("scrollY", window.pageYOffset);
}
document.addEventListener('click', storeScrollPosition);
window.addEventListener('beforeunload', storeScrollPosition);

// restore scroll position on page load
var scrollX = parseInt(sessionStorage.getItem("scrollX") ?? 0);
var scrollY = parseInt(sessionStorage.getItem("scrollY") ?? 0);
if (scrollX != 0 || scrollY != 0) {
    window.scroll(scrollX, scrollY);
}
