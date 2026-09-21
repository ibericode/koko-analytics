// Match PHP's sort_query_string(), which compares percent-encoded parameter names.
export function sortQueryParams(params) {
  var query = params.toString();
  if (query === '') {
    return query;
  }

  return query.split('&').map(function(pair, index) {
    return {
      index: index,
      name: pair.split('=', 1)[0],
      pair: pair,
    };
  }).sort(function(a, b) {
    if (a.name === b.name) {
      return a.index - b.index;
    }
    return a.name < b.name ? -1 : 1;
  }).map(function(item) {
    return item.pair;
  }).join('&');
}
