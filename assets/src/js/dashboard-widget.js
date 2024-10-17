const el = document.getElementById('koko-analytics-dashboard-widget-mount')

function maybeRender() {
  if (!el.clientWidth) {
    return;
  }


}

el.parentElement.style.display = '';
requestAnimationFrame(maybeRender);

/* eslint no-undef: "off" */
if (jQuery) {
  jQuery(document).on('postbox-toggled', maybeRender)
}
