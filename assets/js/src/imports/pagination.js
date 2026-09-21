// Public dashboards use buttons instead of links so crawlers cannot discover every paginated URL.
// Build the target URL only after a visitor activates a pagination control.
document.querySelectorAll('button[data-pagination-key][data-pagination-args]').forEach(function(el) {
  el.addEventListener('click', function() {
    var key = el.getAttribute('data-pagination-key');
    var args = JSON.parse(el.getAttribute('data-pagination-args'));
    var url = new URL(window.location.href);
    var prefix = key + '[';

    // replace only this component's pagination state, preserving the rest of the dashboard view
    Array.from(url.searchParams.keys()).forEach(function(name) {
      if (name.startsWith(prefix)) {
        url.searchParams.delete(name);
      }
    });

    url.searchParams.delete('p');
    Object.keys(args).forEach(function(name) {
      if (args[name] !== null) {
        url.searchParams.set(key + '[' + name + ']', args[name]);
      }
    });
    url.searchParams.sort();
    window.location.href = url.toString();
  });
});
