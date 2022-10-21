let postId = -1 // general site visit

// if this is a visit to a specific page or post, we need to supply the post ID to trackPageview
// so we extract if from the <body> element
const matches = document.body.className.match(/(postid-|page-id-)(\d+)/)
if (matches && matches.length === 3) {
  postId = matches.pop()
}

window.koko_analytics.trackPageview(postId)
