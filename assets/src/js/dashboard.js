// update date_start and date_end <input>'s whenever a preset is selected
var datePresetSelect = document.querySelector('#ka-date-presets');
var dateStartInput = document.querySelector('#ka-date-start');
var dateEndInput = document.querySelector('#ka-date-end');
datePresetSelect.addEventListener('change', function() {
  dateStartInput.disabled = true;
  dateEndInput.disabled = true;
  this.form.submit();
});

// set <select> value for date preset/view to custom whenever date input is used
function setPresetToCustom() {
    datePresetSelect.value = 'custom';
}
dateStartInput.addEventListener('change', setPresetToCustom);
dateStartInput.addEventListener('change', setPresetToCustom);

// fill chart
import {Chart} from './imports/chart.js';
Chart();

// click "prev date range" or "next date range" when using arrow keys
document.addEventListener('keydown', evt => {
    if (evt.key === 'ArrowLeft') {
      document.querySelector('.ka-datepicker--quicknav-prev').click();
    } else if (evt.key === 'ArrowRight') {
      document.querySelector('.ka-datepicker--quicknav-next').click();
    }
  })
