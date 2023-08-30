/**
 * @package koko-analytics
 * @author Danny van Kooten
 * @license GPL-3.0+
 */

// Map variables to global identifiers so that minifier can mangle them to even shorter names
const doc = document
const win = window
const nav = navigator
const enc = encodeURIComponent
const loc = win.location
const body = doc.body
const ka = "koko_analytics"

function getPagesViewed() {
  return (doc.cookie.match(/_koko_analytics_pages_viewed=([^;]+)/) ?? [""]).pop().split('a')
}

function request(url, cb) {
  const img = doc.createElement('img')
  img.style.display = 'none'
  img.onload = () => {
    body.removeChild(img)
    if (cb) cb()
  }
  img.src = win[ka].url + (win[ka].url.indexOf('?') > -1 ? '&' : '?') + url
  body.appendChild(img)
}

function trackPageview (postId) {
  let {dnt, use_cookie, cookie_path, url} = win[ka]

  if (
    // do not track if "Do Not Track" is enabled
    (nav.doNotTrack == 1 && dnt) ||

    // do not track if this is a prerender request
    (doc.visibilityState == 'prerender') ||

    // do not track if user agent looks like a bot
    ((/bot|crawl|spider|seo|lighthouse|preview/i).test(nav.userAgent))
  ) {
    return
  }

  const pagesViewed = getPagesViewed()
  postId += ""
  // first element is always empty string, so if length is 1 it is empty
  let isNewVisitor = pagesViewed.length == 1 ? 1 : 0
  let isUniquePageview = pagesViewed.indexOf(postId) == -1 ? 1 : 0
  let referrer = doc.referrer

  // check if referred by same-site (so definitely a returning visitor)
  if (referrer.indexOf(loc.origin) == 0) {
    isNewVisitor = 0

    // check if referred by same page (so not a unique pageview)
    if (referrer == loc.href) {
      isUniquePageview = 0
    }

    // don't store referrer if from same-site
    referrer = ''
  }

  request(`p=${postId}&nv=${isNewVisitor}&up=${isUniquePageview}&r=${enc(referrer)}&rqp=${Math.random().toString(36)}`, () => {
    if (isUniquePageview) pagesViewed.push(postId)
    if (use_cookie) doc.cookie = `_${ka}_pages_viewed=${pagesViewed.join('a')};SameSite=lax;path=${cookie_path};max-age=21600`
  })
}

function trackEvent(name, params) {
  request(`t=1&e=${name}&p=${enc(JSON.stringify(params ?? {}))}&rqp=${Math.random().toString(36)}`)
}

win.addEventListener('load', () => {
  // window.koko_analytics might be missing if the active theme is not calling wp_head()
  if (win[ka]) {
    trackPageview(win[ka].post_id)
    win[ka] = {...win[ka], trackPageview, trackEvent}
  }
})
