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

Koko Analytics is a privacy-friendly analytics plugin for your WordPress site. It does not need any external services, so any data about your visitors is never shared with a third-party service.

Furthermore, your visitors' privacy is respected. Nothing that could lead back to a specific visitor is tracked and it is easy for visitors to opt-out of tracking altogether using a standardized browser setting called "Do Not Track".

Stop scrolling through pages of reports while collecting sensitive data about your visitors, both of which you probably do not need.
Koko Analytics allows you to focus on what is important and gives you all the essential metrics you need to improve your website, while truly respecting the privacy of your visitors.

### Features

- Plug and play. Just install and activate the plugin and stats will automatically be recorded.
- No external services. Data about visits to your website is yours and yours alone.
- No personal information or anything visitor specific is tracked.
- Blazingly fast. Handles thousands of daily visitors or sudden bursts of traffic without breaking a sweat.
- Counts all the essential statistics that you need: unique site visitors, total site pageviews, visitors and pageviews for individual posts and referrer traffic.
- Option to exclude traffic from logged-in users (by user role).
- Built-in blacklist to combat referrer spam.
- Compatible with pages served from cache.
- GDPR compliant by design.
- Completely open-source (GPLv3 licensed).

You can [contribute to Koko Analytics on GitHub](https://github.com/ibericode/koko-analytics).


== Installation ==

1. In your WordPress admin area, go to **Plugins > New Plugin**, search for **Koko Analytics** and click **Install now**.
1. Alternatively, [download the plugin files](https://downloads.wordpress.org/plugin/koko-analytics.trunk.zip) and upload the contents of `koko-analytics.zip` to your plugins directory, which usually is `/wp-content/plugins/`.
1. Activate the plugin. Koko Analytics will start recording stats right away.
1. Access your analytics by browsing to *Dashboard > Analytics* in your WordPress admin area.

== Frequently Asked Questions ==

#### Does this respect my visitor's privacy?
Absolutely, nothing that could lead back to the visitor is recorded. Furthermore, if the visitor has "Do Not Track" enabled in their browser, this is respected.

### Will this slow down my website?
No, the plugin is built in such a way that it never slows down your website for your visitors. If there is heavy lifting to be done, it is done in a background process.

#### Does this use any external services?
No, the data never leaves your website. That's (part of) what makes Koko Analytics such a great choice if you value true privacy.

#### Why are my pageviews not showing up right away?
The plugin uses a buffer file where pageviews are stored for a very short time before they are permanently stored, as this greatly speeds up the tracking process for sites with many visitors. This means that pageviews will only show up in your dashboard after a 1 minute delay.



== Screenshots ==

1. Koko Analytics' dashboard to view your website statistics.
2. The settings page where you can exclude certain user roles from being counted.


== Changelog ==

#### 1.0 - Nov 4, 2019

Initial release.
