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
const ka = "koko_analytics"

function getPagesViewed() {
  let m = doc.cookie.match(/_koko_analytics_pages_viewed=([^;]+)/)
  return m ? m.pop().split('a') : [];
}

function request(url) {
  return nav.sendBeacon(win[ka].url + (win[ka].url.indexOf('?') > -1 ? '&' : '?') + url)
}

function trackPageview (postId) {
  let {use_cookie, cookie_path} = win[ka]

  if (
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

  request(`p=${postId}&nv=${isNewVisitor}&up=${isUniquePageview}&r=${enc(referrer)}`)
  if (isUniquePageview) pagesViewed.push(postId)
  if (use_cookie) doc.cookie = `_${ka}_pages_viewed=${pagesViewed.join('a')};SameSite=lax;path=${cookie_path};max-age=21600`
}

win[ka].trackPageview = trackPageview;
win.addEventListener('load', () =>  trackPageview(win[ka].post_id))
