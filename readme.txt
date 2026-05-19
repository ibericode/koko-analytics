=== Koko Analytics - Privacy-Friendly WordPress Analytics ===
Contributors: Ibericode, DvanKooten, kokoanalytics
Tags: analytics, google analytics, privacy, statistics, website statistics
Requires at least: 6.0
Tested up to: 7.0
Stable tag: 2.3.6
License: GPL-3.0-or-later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Requires PHP: 7.4

WordPress analytics without cookies or third-party tracking. A fast, privacy-friendly Google Analytics alternative

== Description ==

Koko Analytics gives you WordPress analytics inside your dashboard without sending visitor data to an external service. See your pageviews, visitors, top pages, and referrers while keeping analytics data on your own server.

It is built for privacy from the start. Koko Analytics can be used without cookies and does not process or store personal data. That makes it suitable for GDPR, CCPA, and PECR-friendly website statistics.

It is also built for speed. Koko Analytics adds less than 1 KB to your HTML, works with cached pages, and bypasses WordPress for its optimized collection endpoint.

== Why choose Koko Analytics? ==

= Privacy-friendly analytics =

Koko Analytics is [privacy-friendly analytics](https://www.kokoanalytics.com/privacy-focused-wordpress-analytics/) for WordPress. No personal data is processed or stored, all measurements are anonymous, and nothing is shared with a third-party analytics platform.

= Lightweight website statistics =

Koko Analytics is [lightweight analytics](https://www.kokoanalytics.com/lightweight-wordpress-analytics/). It adds less than 1 kilobyte of data to your HTML and is fully compatible with pages served from any kind of cache. WordPress is bypassed entirely for its collection endpoint, making the impact on your site's performance as close to zero as possible.

= A simple analytics dashboard =

Koko Analytics is [simple analytics](https://www.kokoanalytics.com/simple-wordpress-analytics/). There are no complicated reports to dig through. One dashboard page shows your important website statistics.

= Open source analytics =

Koko Analytics is [open source analytics](https://www.kokoanalytics.com/open-source-wordpress-analytics/) released under the GPL license, just like WordPress itself. It is built in the open, so anyone can verify how it works. No company can lock you in or take your analytics data away.

== Features ==

* **WordPress analytics dashboard** - View your website statistics directly inside WordPress admin.
* **Top posts and pages** - See which content gets the most visits.
* **Referrer statistics** - Find out which websites send traffic to your site.
* **Path-based tracking** - Track statistics for any URL, including archives and search pages.
* **Returning visitor detection** - Reliably detect returning visitors without cookies.
* **Exclusion rules** - Exclude visits from selected WordPress user roles or IP addresses.
* **Historical data imports** - Import statistics from Jetpack Stats, Plausible, or Burst Statistics.
* **Automatic data cleanup** - Remove historical data older than a chosen number of months or years.
* **Popular posts output** - Show your most visited posts or pages with a widget, Gutenberg block, or shortcode.
* **Pageview counter** - Show the total number of pageviews for a page with a shortcode or Gutenberg block.

== Koko Analytics Pro ==

Koko Analytics Pro adds more reporting options for sites that need deeper analytics.

* **Country statistics** - See which countries your visitors come from.
* **Technology reports** - View browser, operating system, and device statistics.
* **Custom event tracking** - Track outbound link clicks, contact form submissions, add-to-cart actions, and more.
* **Email reports** - Receive periodic analytics reports in your inbox.
* **Traffic spike alerts** - Get notified by email when traffic changes quickly.

[View the Koko Analytics live demo](https://www.kokoanalytics.com/koko-analytics-dashboard/) or [see Koko Analytics Pro pricing](https://www.kokoanalytics.com/pricing/).

== Installation ==

1. Go to Plugins > Add New in your WordPress dashboard.
2. Search for Koko Analytics.
3. Click Install Now, then Activate.
4. To upload the plugin manually, download the ZIP from WordPress.org or GitHub, then go to Plugins > Add New > Upload Plugin.
5. After activation, Koko Analytics starts collecting statistics right away. No configuration is required.
6. View your website analytics under Dashboard > Analytics in WordPress admin.

== Frequently Asked Questions ==

= Is Koko Analytics a WordPress analytics plugin? =

Yes. Koko Analytics is a WordPress analytics plugin that shows pageviews, visitors, top pages, and referrers inside your WordPress dashboard.

= Does Koko Analytics use cookies? =

Cookies are optional in Koko Analytics. You can use the plugin without cookies for privacy-friendly website statistics. Read more in the guide: [Does Koko Analytics use cookies?](https://www.kokoanalytics.com/docs/faq/does-koko-analytics-use-cookies/)

= Will Koko Analytics slow down my site? =

No. Koko Analytics adds less than 1 KB to your HTML, works with cached pages, and uses an optimized collection endpoint to keep performance impact as low as possible.

= Is Koko Analytics privacy-friendly? =

Yes. Koko Analytics does not process or store personal data, does not use third-party analytics services, and only stores aggregated counts.

= Is Koko Analytics open source? =

Yes. Koko Analytics is open source analytics released under the GPL license, just like WordPress itself. Anyone can inspect how it works.

= Do I need an account to use Koko Analytics? =

No account is needed. Koko Analytics runs on your own WordPress site, and statistics start recording after activation.

= Does Koko Analytics work with cached pages? =

Yes. Koko Analytics is compatible with pages served from many types of cache.

If your question is not listed here, read the [Koko Analytics documentation](https://www.kokoanalytics.com/docs/).

== Screenshots ==

1. The WordPress analytics dashboard showing visitors, pageviews, top pages, and referrers.
2. Website statistics for the past 2 weeks shown directly after logging in to WordPress.
3. Tracking-related settings for privacy-friendly analytics and visitor statistics.
4. Analytics dashboard settings for customizing the reports shown in WordPress admin.
5. Custom event tracking for outbound links, form submissions, and other analytics events. [Pro]
6. Email reports and traffic spike notifications for Koko Analytics Pro. [Pro]
7. Data ownership tools for importing and exporting your WordPress analytics data.
8. Most viewed posts widget for showing popular content on your site.
9. Country, browser, operating system, and device statistics for deeper website analytics. [Pro]

== Changelog ==

= 2.3.6 =

- dashboard: group top pages by post ID when available, so changing a post slug no longer splits its analytics in the dashboard.
- dashboard: resolve titles and permalinks dynamically for posts and pages in the top pages list.
- dashboard: page-specific totals and charts now use post ID filters when available.
- tracking: ignore query parameters when pretty permalinks are enabled.
- tracking: ensure normalized paths always start with a leading slash.
- data export: improve export reliability by casting numeric values and handling missing tables defensively.
- settings: show database usage and table sizes on the data settings page.
- settings: add current and latest database migration version to debug info.
- release: exclude development-only files from the WordPress.org package.


= 2.3.5 =

- data: ensure aggregation process does not run while database migrations are pending.
- perf: aggregation process no longer invalidates alloptions cache on every run.
- perf: process database pruning in chunks of 10K rows.
- security: add nonce verification to user-initiated action to update to v2.


= 2.3.4 =

- fix: access to Jetpack and Plausible importer pages.
- fix: database warning because of unexisting table on fresh installs.
- ux: table rows selectable again.
- seo: remove canonical URL from public dashboard (because it is already noindex).
- database: change default database purge treshold to 3 years (down from 5).
- dashboard: don't listen to query string argument for public dashboard if pretty permalinks are enabled.


= 2.3.3 =

- database: fix table and column value for upserting new referrer URL's.


= 2.3.2 =

- dashboard: draggable icon now only shows up when hovering table header, not table body
- database: prevent running database migrations concurrently 
- database: try to increase time limit to 300s before running database migrations
- database: re-acquire and extend acquired lock after every individual database migration step

[View the full changelog on GitHub](https://github.com/ibericode/koko-analytics/blob/main/CHANGELOG.md)
