var chart = document.querySelector('#ka-chart-2');
var tooltip = document.querySelector('.ka-chart--tooltip');
if (chart) {
  var bars = chart.querySelectorAll('.bars g');
  var barWidth;

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
}

export function Chart() {
  if (!chart) return;

  var yTicks = chart.querySelectorAll('.axes-y text');
  var leftOffset = 0;
  for (var i = 0; i < yTicks.length; i++) {
    leftOffset = Math.max(leftOffset, 4 + Math.max(5, yTicks[i].textContent.length * 8));
  }
  leftOffset += 4;
  var tickWidth = (chart.clientWidth - leftOffset) / bars.length;
  barWidth = tickWidth - 2;

  for (i = 0; i < bars.length; i++) {
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
}


