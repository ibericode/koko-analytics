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

function trackPageview (postId) {
  let {dnt, use_cookie, cookie_path, url} = win[ka]
  postId = String(postId)

  if (
    // do not track if "Do Not Track" is enabled
    (nav.doNotTrack === '1' && dnt) ||

    // do not track if this is a prerender request
    (doc.visibilityState === 'prerender') ||

    // do not track if user agent looks like a bot
    ((/bot|crawl|spider|seo|lighthouse|preview/i).test(nav.userAgent))
  ) {
    return
  }

  const pagesViewed = getPagesViewed()
  // first element is always empty string, so if length is 1 it is empty
  let isNewVisitor = +pagesViewed.length === 1
  let isUniquePageview = +pagesViewed.indexOf(postId) === -1
  let referrer = doc.referrer

  // check if referred by same-site (so definitely a returning visitor)
  if (referrer.indexOf(loc.origin) === 0) {
    isNewVisitor = 0

    // check if referred by same page (so not a unique pageview)
    if (referrer === loc.href) {
      isUniquePageview = 0
    }

    // don't store referrer if from same-site
    referrer = ''
  }

  const img = doc.createElement('img')
  img.style.display = 'none'
  img.onload = () => {
    body.removeChild(img)
    isUniquePageview && pagesViewed.push(postId)
    if (use_cookie) doc.cookie = `_${ka}_pages_viewed=${pagesViewed.join('a')};SameSite=lax;path=${cookie_path};max-age=21600`
  }

  // build tracker URL
  img.src = url + `${url.indexOf('?') > -1 ? '&' : '?'}p=${postId}&nv=${isNewVisitor}&up=${isUniquePageview}&r=${enc(referrer)}&rqp=${Math.random().toString(36)}`
  body.appendChild(img)
}

function init () {
  // window.koko_analytics might be missing if the active theme is not calling wp_head()
  if (!win[ka]) return
  trackPageview(win[ka].post_id)
  win[ka].trackPageview = trackPageview
}

win.addEventListener('load', init)
