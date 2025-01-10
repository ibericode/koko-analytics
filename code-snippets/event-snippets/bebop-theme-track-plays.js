document.addEventListener('click', function(evt) {
  if (evt.target.classList && evt.target.classList.contains("btn-play")) {
    window.koko_analytics.trackEvent("Podcast Play", evt.target.getAttribute("data-play-id"));
  }
});
