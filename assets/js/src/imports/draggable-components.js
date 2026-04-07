
// drag-and-drop sorting for dashboard components
var container = document.getElementById('ka-components');
if (container && container.querySelector('.ka-col[draggable]')) {
  var dragEl = null;
  var mouseDownTarget = null;

  container.addEventListener('mousedown', function(evt) {
    mouseDownTarget = evt.target;
  });

  container.addEventListener('dragstart', function(evt) {
    if (!mouseDownTarget || !mouseDownTarget.closest('th')) { 
      evt.preventDefault(); 
      return; 
    }
    dragEl = evt.target.closest('.ka-col[draggable]');
    if (!dragEl) return;
    dragEl.classList.add('ka-dragging');
    evt.dataTransfer.effectAllowed = 'move';
  });

  container.addEventListener('dragover', function(evt) {
    evt.preventDefault();
    evt.dataTransfer.dropEffect = 'move';
    var target = evt.target.closest('.ka-col[draggable]');
    if (!target || target === dragEl) return;

    // determine if dragEl is currently before or after target in DOM
    // if dragEl is before target, insert dragEl after target (and vice versa)
    if (dragEl.compareDocumentPosition(target) & Node.DOCUMENT_POSITION_FOLLOWING) {
      container.insertBefore(dragEl, target.nextSibling);
    } else {
      container.insertBefore(dragEl, target);
    }
  });

  container.addEventListener('dragend', function() {
    if (!dragEl) { 
      return;
    }
    
    dragEl.classList.remove('ka-dragging');
    dragEl = null;

    // collect new order and save
    var order = Array.from(container.querySelectorAll('.ka-col[draggable]')).map(el => el.id);
    var body = new FormData();
    body.append('koko_analytics_action', 'save_component_order');
    body.append('_nonce', container.dataset.nonce);
    order.forEach(function(id) {
      body.append('component_order[]', id);
    });
    window.fetch(window.location.href, { method: 'POST', body: body });
  });
}