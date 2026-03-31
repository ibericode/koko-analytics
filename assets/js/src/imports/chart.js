function Chart(root) {
  this.root = root;
  this.barWidth = 0;
  this.closeTooltipTimeout = null;
  
  if (!this.root) {
    return;
  }

  // move tooltip to be a direct child of body
  this.tooltip = document.querySelector('.ka-chart--tooltip');
  this.tooltip.remove();
  document.body.appendChild(this.tooltip);

  // event listeners for removing tooltip
  [this.root, this.tooltip].forEach(el => {
    el.addEventListener('mouseleave', this.closeTooltip.bind(this));
    el.addEventListener('mouseenter', this.keepTooltip.bind(this));
  });
  
  // event listener for showing tooltip
  this.root.addEventListener('mouseover', (e) => {
    if (e.target.tagName !== 'rect') {
      return;
    }

    this.showTooltip(e.target);
  })

  // initial draw (sets correct bar width)
  this.redraw();
}

Chart.prototype.closeTooltip = function() {
  this.closeTooltipTimeout = window.setTimeout(() => {
    this.tooltip.style.display = 'none';
  }, 20);
}

Chart.prototype.keepTooltip = function() {
  window.clearTimeout(this.closeTooltipTimeout);
}

Chart.prototype.showTooltip = function(barElement) {
  // update tooltip content
    const data = barElement.parentElement.dataset;
    this.tooltip.querySelector('.ka-chart--tooltip-heading').textContent = data.date;
    this.tooltip.querySelector('.ka--visitors').children[0].textContent = data.visitors;
    this.tooltip.querySelector('.ka--pageviews').children[0].textContent = data.pageviews;

    // set tooltip position relative to top-left of document
    const availWidth = document.body.clientWidth;
    this.tooltip.style.display = 'block';
    const styles = barElement.parentElement.getBoundingClientRect() // <g> element
    let left = Math.round(styles.left - 0.5 * this.tooltip.clientWidth + 0.5 * this.barWidth);
    let top = Math.round(styles.top + window.scrollY - this.tooltip.clientHeight);
    let offCenter = 0;

    // if tooltip goes off the screen, position it a bit off center
    if (left < 12) {
      offCenter = -left + 12;
    } else if (left + this.tooltip.clientWidth > availWidth - 12) {
      offCenter = availWidth - (left + this.tooltip.clientWidth) - 12;
    }

    // shift tooltip to the right (or left)
    left += offCenter;

    // shift arrow to the left (or right)
    const arrow = this.tooltip.querySelector('.ka-chart--tooltip-arrow');
    arrow.style.marginLeft = offCenter === 0 ? 'auto' : ((0.5 * this.tooltip.clientWidth) - 6 - offCenter) + 'px';
    this.tooltip.style.left = left + 'px';
    this.tooltip.style.top = top + 'px';
}

Chart.prototype.redraw = function() {
  const leftOffset = Array.from(this.root.querySelectorAll('.axes-y text')).reduce((carry, tick) => Math.max(carry, 8 + Math.max(5, tick.textContent.length * 8)), 0);  
  const bars = this.root.querySelectorAll('.bars g');
  const tickWidth = Math.max(1, (this.root.clientWidth - leftOffset) / bars.length);
  this.barWidth = Math.max(1, tickWidth - 2);

  // update width of each bar now that we know the client width
  for (let i = 0; i < bars.length; i++) {
    const x = i * tickWidth + leftOffset + 1;
    const children = bars[i].children;

    // pageviews <rect>
    children[0].setAttribute('x', x);
    children[0].setAttribute('width', this.barWidth);

    // visitors <rect>
    children[1].setAttribute('x', x);
    children[1].setAttribute('width', this.barWidth);

    // tick <line>
    const xTick = i * tickWidth + leftOffset + 0.5 * tickWidth;
    children[2].style.display = this.barWidth === 1 ? 'none' : '';
    children[2].setAttribute('x1', xTick);
    children[2].setAttribute('x2', xTick);
  }
}

export {Chart};