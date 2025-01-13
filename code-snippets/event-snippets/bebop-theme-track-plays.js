// Event snippet for Koko Analytics Pro to track all clicks on play buttons in the Bebop theme
// This specific example tracks events with the name "Podcast Play"
// Theme: https://themeforest.net/item/bepop-nonstop-music-wordpress-theme/24075935

document.addEventListener('click', function(evt) {
  if (evt.target.classList && evt.target.classList.contains("btn-play")) {
    var title = evt.target.closest('article')?.querySelector('.entry-title')?.textContent ?? evt.target.getAttribute("data-play-id");
    window.koko_analytics.trackEvent("Podcast Play", title);
  }
});
