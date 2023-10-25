const {nonce, root} = window.koko_analytics
/**
 *
 * @param {string} path
 * @param {object} params
 * @returns {Promise<any>}
 */
export function request (path, params = {}) {
  let url = root + 'koko-analytics/v1' + path  + '?' + (new URLSearchParams(params))

  return fetch(url, {
    headers: {
      'X-WP-Nonce': nonce,
      Accepts: 'application/json'
    },
    credentials: 'same-origin'
  }).then(r => {
    // reject response when status is not ok-ish
    if (r.status >= 400) {
      console.error('Koko Analytics encountered an error trying to request data from the REST endpoints. Please check your PHP error logs for the error that occurred.')
      throw new Error(r.statusText)
    }

    return r
  }).then(r => r.json())
}

// Nonces are valid for 24 hours, whereas WP Admin sessions can live for much longer.
// So here we reload the page every 12 hours so that we get a new X-WP-Nonce that is guaranteed to work.
window.setTimeout(() => {
  window.location.reload()
}, 12 * 60 * 60 * 1000);
