const {nonce, root} = window.koko_analytics
/**
 *
 * @param {string} path
 * @param {object} opts
 * @returns {Promise<any>}
 */
export function request (path, opts = {}) {
  Object.assign(opts, {
    headers: {
      'X-WP-Nonce': nonce,
      Accepts: 'application/json'
    },
    credentials: 'same-origin'
  })

  let url = root + 'koko-analytics/v1' + path
  if (opts.body) {
    // allow passing "body" option for GET requests, convert it to query params
    if (!opts.method || opts.method === 'GET') {
      url += url.indexOf('?') ? '&' : '?'

      for (const key in opts.body) {
        url += encodeURIComponent(key) + '=' + encodeURIComponent(opts.body[key]) + '&'
      }
      url = url.slice(0, -1)
      delete opts.body
    }

    if (opts.method === 'POST') {
      opts.headers['Content-Type'] = 'application/json'

      if (typeof opts.body !== 'string') {
        opts.body = JSON.stringify(opts.body)
      }
    }
  }

  return fetch(url, opts).then(r => {
    // reject response when status is not ok-ish
    if (r.status >= 400) {
      throw new Error(r.statusText)
    }

    return r
  }).then(r => r.json())
}
