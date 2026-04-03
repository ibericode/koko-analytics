// every 61 seconds without mouse activity, reload the page (but only if tab is active)
let reloadTimeout = window.setTimeout(reloadIfActive, 61000);

function reloadIfActive() {
  if (!document.hidden) {
    window.location.reload();
  } else {
    // if document hidden, try again in 61s
    reloadTimeout = window.setTimeout(reloadIfActive, 61000);
  }
}

document.addEventListener('mouseover', function() {
  window.clearTimeout(reloadTimeout);
  reloadTimeout = window.setTimeout(reloadIfActive, 61000);
})