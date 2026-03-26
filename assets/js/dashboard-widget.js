import {Chart} from './imports/chart.js';

var el = document.getElementById('ka-chart')
function maybeRender() {
  if (!el.clientWidth) {
    return;
  }

  Chart();
}

el.parentElement.style.display = '';
requestAnimationFrame(maybeRender);

/* eslint no-undef: "off" */
if (jQuery) {
  jQuery(document).on('postbox-toggled', maybeRender)
}
