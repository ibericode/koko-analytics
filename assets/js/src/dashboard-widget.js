import { Chart } from './imports/chart.js';

const el = document.getElementById('ka-chart')
const chart = new Chart(el);

function maybeRender() {
  if (!el.clientWidth) {
    return;
  }

  chart.redraw();
}

el.parentElement.style.display = '';
requestAnimationFrame(maybeRender);

/* eslint no-undef: "off" */
if (jQuery) {
  jQuery(document).on('postbox-toggled', maybeRender)
}