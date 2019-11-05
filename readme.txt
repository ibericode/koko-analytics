=== Koko Analytics ===
Contributors: Ibericode, DvanKooten
Donate link: https://kokoanalytics.com/#utm_source=wp-plugin-repo&utm_medium=koko-analytics&utm_campaign=donate-link
Tags: analytics, statistics, stats
Requires at least: 4.5
Tested up to: 5.3
Stable tag: 1.0
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Requires PHP: 5.3

Privacy-friendly analytics for your WordPress site

== Description ==

Koko Analytics is a privacy-friendly analytics plugin for your WordPress site. It does not need any external services, so the data about your visitors never leaves your site.

Your visitors' privacy is respected so that nothing is tracked that could lead back to one specific visitor.

### Features

- No external services. Data is yours and yours alone.
- No personal information or anything visitor specific is tracked.
- Blazingly fast. Handles thousands of daily visitors or sudden bursts of traffic without breaking a sweat.
- Plug and play. Just install and activate the plugin and stats will automatically be recorded.
- Counts all the essential statistics that you need: unique site visitors, total site pageviews, visitors and pageviews for individual posts and referrer traffic.
- Option to exclude traffic from logged-in users (by user role).
- Built-in blacklist to combat referrer spam.
- Compatible with pages served from cache.
- GDPR compliant by design.
- Completely open-source (GPLv3 licensed).

You can [contribute to Koko Analytics on GitHub](https://github.com/ibericode/koko-analytics).


== Installation ==

1. In your WordPress admin panel, go to *Plugins > New Plugin*, search for **Koko Analytics** and click "*Install now*"
1. Alternatively, download the plugin and upload the contents of `koko-analytics.zip` to your plugins directory, which usually is `/wp-content/plugins/`.
1. Activate the plugin


== Frequently Asked Questions ==

#### Does this respect my visitor's privacy?
Absolutely, nothing that could lead back to the visitor is recorded. Furthermore, if the visitor has "Do Not Track" enabled in their browser, this is respected.

### Will this slow down my website?
No, the plugin is built in such a way that it never slows down your website for your visitors. All heavy lifting occurs in a background process.

#### Does this use any external services?
No, the data never leaves your website.

#### Why are my pageviews not showing up right away?
The plugin uses a buffer file where pageviews are stored for a very short time before they are permanently stored, as this greatly speeds up the tracking process for sites with many visitors. This means that pageviews will only show up in your dashboard after a 1 minute delay.



== Screenshots ==

1. The dashboard to view your website statistics.


== Changelog ==

#### 1.0 - Nov 4, 2019

Initial release.
