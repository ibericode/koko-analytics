
// drag-and-drop sorting for dashboard components
let container = document.getElementById('ka-components');
if (container && container.querySelector('.ka-col[data-sortable]')) {
  let dragEl = null;
  let cols = container.querySelectorAll('.ka-col[data-sortable]');

  // only enable draggable when mousedown occurs on a th (the drag handle)
  container.addEventListener('mousedown', function(evt) {
    if (evt.target.closest('th')) {
      let col = evt.target.closest('.ka-col');
      if (col) { 
        col.draggable = true; 
      }
    }
  });

  document.addEventListener('mouseup', function() {
    cols.forEach(col => { 
      col.draggable = false; 
    });
  });

  container.addEventListener('dragstart', function(evt) {
    dragEl = evt.target.closest('.ka-col');
    if (!dragEl) {
      return;
    }
    dragEl.classList.add('ka-dragging');
    evt.dataTransfer.effectAllowed = 'move';
  });

  container.addEventListener('dragover', function(evt) {
    evt.preventDefault();
    evt.dataTransfer.dropEffect = 'move';
    let target = evt.target.closest('.ka-col');
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

    // reset draggable
    cols.forEach(function(col) { col.draggable = false; });

    // collect new order and save
    let order = Array.from(cols).map(el => el.id);
    let body = new FormData();
    body.append('koko_analytics_action', 'save_component_order');
    body.append('_nonce', container.dataset.nonce);
    order.forEach((id) => {
      body.append('component_order[]', id);
    });
    window.fetch(window.location.href, { method: 'POST', body: body });
  });
}