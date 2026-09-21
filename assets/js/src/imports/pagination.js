import { sortQueryParams } from './query-string.js';

// Public dashboards use buttons instead of links so crawlers cannot discover every paginated URL.
// Build the target URL only after a visitor activates a pagination control.
document.querySelectorAll('button[data-pagination-key][data-pagination-query]').forEach(function(el) {
  el.addEventListener('click', function() {
    var key = el.getAttribute('data-pagination-key');
    var targetParams = new URLSearchParams(el.getAttribute('data-pagination-query'));
    var url = new URL(window.location.href);
    var prefix = key + '[';

    // replace only this component's pagination state, preserving the rest of the dashboard view
    Array.from(url.searchParams.keys()).forEach(function(name) {
      if (name.startsWith(prefix)) {
        url.searchParams.delete(name);
      }
    });

    url.searchParams.delete('p');
    targetParams.forEach(function(value, name) {
      url.searchParams.set(name, value);
    });
    url.search = sortQueryParams(url.searchParams);
    window.location.href = url.toString();
  });

  el.closest('[data-pagination-controls]').hidden = false;
});
