// update date_start and date_end <input>'s whenever a preset is selected
var datePresetSelect = document.querySelector('#ka-date-presets');
var dateStartInput = document.querySelector('#ka-date-start');
var dateEndInput = document.querySelector('#ka-date-end');
datePresetSelect && datePresetSelect.addEventListener('change', function() {
  dateStartInput.disabled = true;
  dateEndInput.disabled = true;
  this.form.submit();
});

// set <select> value for date preset/view to custom whenever date input is used
function setPresetToCustom() {
  datePresetSelect.value = 'custom';
}

dateStartInput && dateStartInput.addEventListener('change', setPresetToCustom);
dateEndInput && dateEndInput.addEventListener('change', setPresetToCustom);

// fill chart
import {Chart} from './imports/chart.js';
Chart();

// click "prev date range" or "next date range" when using arrow keys
document.addEventListener('keydown', function (evt) {
  if (evt.defaultPrevented) {
    return; // Do nothing if the event was already processed
  }

  switch (evt.key) {
  case 'ArrowLeft':
    document.querySelector('.js-quicknav-prev').click();
    break;
  case 'ArrowRight':
    document.querySelector('.js-quicknav-next').click();
    break;
  }
})

// every 61 seconds without mouse activity, reload the page (but only if tab is active)
var reloadTimeout = window.setTimeout(reloadIfActive, 61000);
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

// fake <a> elements to stop bots from crawling infinitely
document.querySelectorAll('a[data-href]').forEach(function(el) {
  el.addEventListener('click', function(evt) {
    evt.preventDefault();
    window.location.href = el.getAttribute('data-href');
  });
});
