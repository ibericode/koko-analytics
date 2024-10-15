
// update date_start and date_end <input>'s whenever a preset is selected
var datePresetSelect = document.querySelector('#ka-date-presets');
var dateStartInput = document.querySelector('#ka-date-start');
var dateEndInput = document.querySelector('#ka-date-end');
datePresetSelect.addEventListener('change', function() {
  var data = this.selectedOptions[0].dataset;
  dateStartInput.value = data.startDate;
  dateEndInput.value = data.endDate;
  this.form.submit();
});

// fill chart
import Chart from './imports/chart.js'
let { startDate, endDate, data } = window.koko_analytics
let page = 0;
Chart(document.querySelector('#ka-chart'), data.chart, startDate, endDate, page);

// click "prev date range" or "next date range" when using arrow keys
document.addEventListener('keydown', evt => {
    if (evt.key === 'ArrowLeft') {
      document.querySelector('.ka-datepicker--quicknav-prev').click();
    }

    if (evt.key === 'ArrowRight') {
      document.querySelector('.ka-datepicker--quicknav-next').click();
    }
  })
