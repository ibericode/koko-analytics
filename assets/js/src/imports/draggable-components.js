
// drag-and-drop sorting for dashboard components
if (window.koko_analytics_sortable) {
  var container = document.getElementById('ka-components');
  var dragEl = null;

  container.addEventListener('dragstart', function(evt) {
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
    const order = Array.from(container.querySelectorAll('.ka-col[draggable]')).map(el => el.id);
    let body = new FormData();
    body.append('koko_analytics_action', 'save_component_order');
    body.append('_nonce', window.koko_analytics_sortable.nonce);
    order.forEach((id) => {
      body.append('component_order[]', id);
    });
    window.fetch(window.location.href, { method: 'POST', body: body });
  });
}