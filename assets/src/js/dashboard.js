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
var chart = document.querySelector('#ka-chart-2');
var bars = chart.querySelectorAll('.bars g');
var leftOffset = 44; // TODO: Grab dynamically based on char count from y-axes?
var tickWidth = (chart.clientWidth - leftOffset) / bars.length;
var barWidth = tickWidth - 2;
var tooltip = document.querySelector('.ka-chart--tooltip');
for (var i = 0; i < bars.length; i++) {
  var x = i * tickWidth + leftOffset + 1;

  // pageviews <rect>
  bars[i].children[0].setAttribute('x', x);
  bars[i].children[0].setAttribute('width', barWidth);

  // visitors <rect>
  bars[i].children[1].setAttribute('x', x);
  bars[i].children[1].setAttribute('width', barWidth);

  // tick <line>
  x = i * tickWidth + leftOffset + 0.5 * tickWidth;
  bars[i].children[2].setAttribute('x1', x);
  bars[i].children[2].setAttribute('x2', x);
}

chart.addEventListener('mouseover', function(e) {
  if (e.target.tagName !== 'rect') {
    tooltip.style.display = 'none'
    return;
  }

  var data = e.target.parentElement.dataset;
  tooltip.querySelector('.ka-chart--tooltip-heading').textContent = data.date;
  tooltip.querySelector('.ka--visitors').children[0].textContent = data.visitors;
  tooltip.querySelector('.ka--pageviews').children[0].textContent = data.pageviews;
  tooltip.style.display = 'block';
  var styles = e.target.parentElement.getBoundingClientRect()
  var left = Math.round(styles.x - 0.5 * tooltip.clientWidth + 0.5 * barWidth) + 'px';
  var top = Math.round(styles.y - tooltip.clientHeight) + 'px';
  tooltip.style.left = left;
  tooltip.style.top = top;
})


// click "prev date range" or "next date range" when using arrow keys
document.addEventListener('keydown', evt => {
    if (evt.key === 'ArrowLeft') {
      document.querySelector('.ka-datepicker--quicknav-prev').click();
    } else if (evt.key === 'ArrowRight') {
      document.querySelector('.ka-datepicker--quicknav-next').click();
    }
  })
