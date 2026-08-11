
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

// fake <a> elements to stop bots from crawling infinitely
// the HTML only carries the target dates, we assemble the URL here so the document holds no crawlable link
// note we keep just the query args identifying the dashboard itself, dropping any active filter or pagination
document.querySelectorAll('a[data-start-date][data-end-date]').forEach(function(el) {
  el.addEventListener('click', function(evt) {
    evt.preventDefault();

    var url = new URL(window.location.href);
    var params = new URLSearchParams();
    ['page', 'koko-analytics-dashboard'].forEach(function(key) {
      if (url.searchParams.has(key)) {
        params.set(key, url.searchParams.get(key));
      }
    });
    params.set('start_date', el.getAttribute('data-start-date'));
    params.set('end_date', el.getAttribute('data-end-date'));
    url.search = params.toString();
    window.location.href = url.toString();
  });
});