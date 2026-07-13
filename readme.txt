=== Koko Analytics - Privacy-Friendly WordPress Analytics ===
Contributors: Ibericode, DvanKooten, kokoanalytics
Tags: analytics, google analytics, privacy, statistics, website statistics
Requires at least: 6.2
Tested up to: 7.0
Stable tag: 2.5.1
License: GPL-3.0-or-later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Requires PHP: 7.4

Pivacy-friendly and lightweight Google Analytics alternative for WordPress sites. No cookies, just insights.

== Description ==

Koko Analytics is a privacy-friendly and lightweight Google Analytics alternative for WordPress sites. It is simple to use, does not rely on any external services and does not intrude on the privacy of your visitors.

== Why choose Koko Analytics? ==

= Important insights =

Koko Analytics provides you with [simple and effective analytics](https://www.kokoanalytics.com/simple-wordpress-analytics/#utm_source=wordpress.org&utm_medium=link&utm_campaign=free-plugin). There are no complicated reports to dig through. All the important metrics are visible from a quick glance at your analytics dashboard.

= Privacy-friendly analytics =

Koko Analytics is a [privacy-friendly analytics](https://www.kokoanalytics.com/privacy-focused-wordpress-analytics/#utm_source=wordpress.org&utm_medium=link&utm_campaign=free-plugin) solution for WordPress. It can be used without any cookies, no personal data is processed or stored and nothing is shared with any third-party. That makes it suitable for GDPR, CCPA, and PECR-compliant website statistics.

= Lightweight website statistics =

Koko Analytics is [lightweight analytics](https://www.kokoanalytics.com/lightweight-wordpress-analytics/#utm_source=wordpress.org&utm_medium=link&utm_campaign=free-plugin). It adds less than 1 kilobyte of data to your pages and is fully compatible with all kinds of caching setups. WordPress is bypassed entirely for its collection endpoint, making the impact on your site's performance as close to zero as possible.

= Open source analytics =

Koko Analytics is [open source analytics](https://www.kokoanalytics.com/open-source-wordpress-analytics/#utm_source=wordpress.org&utm_medium=link&utm_campaign=free-plugin) released under the GPL license, just like WordPress itself. The source code is publicly available. Everyone has the right to run, study, share or modify the source code. 

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

[View the Koko Analytics live demo](https://www.kokoanalytics.com/koko-analytics-dashboard/#utm_source=wordpress.org&utm_medium=link&utm_campaign=free-plugin) or [see Koko Analytics Pro pricing](https://www.kokoanalytics.com/pricing/#utm_source=wordpress.org&utm_medium=link&utm_campaign=free-plugin).

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

Cookies are optional in Koko Analytics. You can use the plugin without cookies for privacy-friendly website statistics. Read more in the guide: [Does Koko Analytics use cookies?](https://www.kokoanalytics.com/docs/faq/does-koko-analytics-use-cookies/#utm_source=wordpress.org&utm_medium=link&utm_campaign=free-plugin)

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

= Where do I report security bugs found in this plugin? =

Please report security bugs found in the source code of the plugin through the [Patchstack Vulnerability Disclosure Program](https://patchstack.com/database/vdp/9e5fc096-a451-400e-b839-ee267aaf3bcb). The Patchstack team will assist you with verification, CVE assignment, and notify the developers of this plugin.

= Other questions =

If your question is not listed here, read the [Koko Analytics documentation](https://www.kokoanalytics.com/docs/#utm_source=wordpress.org&utm_medium=link&utm_campaign=free-plugin).

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

= 2.5.1 =

- tracking: more aggressive bot filter lists.
- dashboard: add new Koko Analytics logo and brand colors.


= 2.5.0 =

- import: add importers for Burst Statistics, Statify, WP Statistics, Independent Analytics, and SlimStat Analytics.
- import: fix updating existing referrer statistics during imports.
- dashboard: improve the styling of KPI metrics, charts, tables, pagination, and the Pro upsell.
- dashboard: dim the realtime component when filtering by page.
- data: run database migrations immediately after resetting tables.


= 2.4.1 =

- import: fix Plausible importer compatibility with PHP 7.4.
- security: harden data import validation and output escaping.
- dashboard: fix collation mismatch when querying by path containing utf-8 characters.
- dashboard: return a 403 response and prevent indexing when visited by bots or crawlers.
- compatibility: raise the minimum supported WordPress version to 6.2.


= 2.4.0 =

- tracking: hook into the visibilitychange event again to ignore prerender requests.
- data: rewrite exporter and importer to use NDJSON instead of raw SQL.
- import: fix column names for the referrer stats table.
- rest: clamp date range for unauthenticated users to prevent large table scans.
- review notice: simplify the review notice to one primary action.


= 2.3.7 =

- tracking: include UTM parameters in pageview tracking requests so integrations can access campaign data.
- endpoint: harden pageview and event request validation by checking required parameters and accepted types.
- endpoint: unslash request data when running inside WordPress.
- endpoint: use exact buffer filename matching when finding existing buffer files.
- endpoint: use file locking when writing to buffer and session files.
- endpoint: handle missing upload or sessions directories more defensively.
- dashboard: fix saving component order when the page contains non-sortable dashboard columns.
- dashboard: keep an explicit group parameter when switching chart grouping back to days.
- docs: add campaign parameters to in-plugin links to Koko Analytics documentation and Pro pages.

[View the full changelog on GitHub](https://github.com/ibericode/koko-analytics/blob/main/CHANGELOG.md)
