import fetch from 'unfetch'
import 'promise-polyfill/src/polyfill'

const vars = window.koko_analytics

function request (path, opts = {}) {
  opts.headers = {
    'X-WP-Nonce': vars.nonce,
    Accepts: 'application/json'
  }
  opts.credentials = 'same-origin'

  let url = vars.root + 'koko-analytics/v1' + path

  if (opts.body) {
    // allow passing "body" option for GET requests, convert it to query params
    if (!opts.method || opts.method === 'GET') {
      if (url.indexOf('?') < 0) {
        url += '?'
      } else {
        url += '&'
      }
      for (const key in opts.body) {
        url += `${window.encodeURIComponent(key)}=${window.encodeURIComponent(opts.body[key])}&`
      }
      url = url.substring(0, url.length - 1)
      delete opts.body
    }

    if (opts.method === 'POST') {
      opts.headers['Content-Type'] = 'application/json'

      if (typeof (opts.body) !== 'string') {
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

// pad number with zero's (for use in formatDate function below)
const pad = d => d < 10 ? '0' + d : d

function formatDate (d) {
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`
}

export default { request, formatDate }
