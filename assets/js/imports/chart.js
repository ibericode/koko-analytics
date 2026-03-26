export function Chart() {
  const chart = document.querySelector('#ka-chart');
  if (! chart) {
    return;
  }

  const tooltip = document.querySelector('.ka-chart--tooltip');
  const arrow = document.querySelector('.ka-chart--tooltip-arrow');
  const bars = chart.querySelectorAll('.bars g');
  let barWidth, closeTooltipTimeout;

  tooltip.remove();
  document.body.appendChild(tooltip);

  // event listeners for removing tooltip
  [chart, tooltip].forEach(el => {
    el.addEventListener('mouseleave',  () => {
      closeTooltipTimeout = window.setTimeout(function() {
        tooltip.style.display = 'none';
      }, 20);
    });
    el.addEventListener('mouseenter', () => {
      window.clearTimeout(closeTooltipTimeout);
    })
  });
  

  // event listener for showing tooltip
  chart.addEventListener('mouseover', (e) => {
    if (e.target.tagName !== 'rect') {
      return;
    }

    // update tooltip content
    const data = e.target.parentElement.dataset;
    tooltip.querySelector('.ka-chart--tooltip-heading').textContent = data.date;
    tooltip.querySelector('.ka--visitors').children[0].textContent = data.visitors;
    tooltip.querySelector('.ka--pageviews').children[0].textContent = data.pageviews;

    // set tooltip position relative to top-left of document
    const availWidth = document.body.clientWidth;
    tooltip.style.display = 'block';
    const styles = e.target.parentElement.getBoundingClientRect() // <g> element
    let left = Math.round(styles.left - 0.5 * tooltip.clientWidth + 0.5 * barWidth);
    let top = Math.round(styles.top + window.scrollY - tooltip.clientHeight);
    let offCenter = 0;

    // if tooltip goes off the screen, position it a bit off center
    if (left < 12) {
      offCenter = -left + 12;
    } else if (left + tooltip.clientWidth > availWidth - 12) {
      offCenter = availWidth - (left + tooltip.clientWidth) - 12;
    }

    // shift tooltip to the right (or left)
    left += offCenter;

    // shift arrow to the left (or right)
    arrow.style.marginLeft = offCenter === 0 ? 'auto' : ((0.5 * tooltip.clientWidth) - 6 - offCenter) + 'px';
    tooltip.style.left = left + 'px';
    tooltip.style.top = top + 'px';
  })

  const yTicks = chart.querySelectorAll('.axes-y text');
  let leftOffset = 0;
  for (let i = 0; i < yTicks.length; i++) {
    leftOffset = Math.max(leftOffset, 8 + Math.max(5, yTicks[i].textContent.length * 8));
  }
  const tickWidth = Math.max(1, (chart.clientWidth - leftOffset) / bars.length);
  barWidth = Math.max(1, tickWidth - 2);

  // update width of each bar now that we know the client width
  for (let i = 0; i < bars.length; i++) {
    const x = i * tickWidth + leftOffset + 1;
    const children = bars[i].children;

    // pageviews <rect>
    children[0].setAttribute('x', x);
    children[0].setAttribute('width', barWidth);

    // visitors <rect>
    children[1].setAttribute('x', x);
    children[1].setAttribute('width', barWidth);

    // tick <line>
    const xTick = i * tickWidth + leftOffset + 0.5 * tickWidth;
    children[2].style.display = barWidth === 1 ? 'none' : '';
    children[2].setAttribute('x1', xTick);
    children[2].setAttribute('x2', xTick);
  }
}


