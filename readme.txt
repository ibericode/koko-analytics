=== Koko Analytics - Privacy-Friendly Statistics for WordPress ===
Contributors: Ibericode, DvanKooten
Donate link: https://www.kokoanalytics.com/pricing/
Tags: statistics, analytics, stats, analytics alternative
Requires at least: 6.0
Tested up to: 6.8
Stable tag: 1.8.6
License: GPL-3.0-or-later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Requires PHP: 7.4

Lightweight, privacy-friendly website analytics & statistics for WordPress. No cookies, no personal data, 100% GDPR/CCPA compliant.

== Description ==

= Simple, privacy-first analytics and statistics for WordPress =

Koko Analytics is a lightweight, privacy-friendly statistics plugin that runs entirely on your WordPress site.
No third-party services. No personal data collection. No cookies required. Just the essential analytics you need — all under your control.

Whether you want to track visits, see your most popular pages, or understand where your visitors are coming from, Koko Analytics makes it easy without compromising privacy or slowing down your site.

**Why Koko Analytics?**

- **Instant setup** – Activate the plugin and stats start collecting right away.
- **Privacy-friendly by design** – GDPR and CCPA compliant, with no personal data tracking.
- **Own your data** – All statistics are stored on your server only.
- **Fast and lightweight** – Adds less than 500 bytes of JavaScript and handles high traffic with ease.
- **No cookies needed** – Choose cookie-based, cookieless, or no tracking at all.
- **Accurate essentials** – Tracks pageviews, unique pageviews, and referral URLs.

**Perfect for site owners who want clear, reliable website analytics without giving away visitor data to advertising companies.**

You can view a [live demo here](https://www.kokoanalytics.com/?koko-analytics-dashboard).

Koko Analytics is 100% free and open-source — no account required.

### Features

- **Plug and play**: After installing and activating, statistics are collected immediately.
- **No external services**: All data stays on your server.
- **No personal data**: Nothing specific to any visitor is stored.
- **No cookies**: Choose between cookie-based, cookieless, or no tracking.
- **Fast**: Handles hundreds of concurrent visitors without slowing down your site.
- **Lightweight**: Adds less than 500 bytes of JavaScript to your pages.
- **Efficient storage**: A full year of data takes up less than 10 MB in your database.
- **GDPR Compliant** by design.
- **Essential metrics**: Pageviews, unique pageviews, referral URLs.
- **Referrer spam protection**: Built-in blocklist to filter bad data.
- **REST API**: Access your analytics data programmatically.
- **Cache-friendly**: Works with browser and server caching.
- **Open-source**: [View on GitHub](https://github.com/ibericode/koko-analytics).
- **AMP support**: Tracks AMP pages using the official AMP plugin.
- **Import from other plugins**: Migrate data from Jetpack Stats or Burst Statistics.

### Upgrade to Koko Analytics Pro

All of the above features are free. With [Koko Analytics Pro](https://www.kokoanalytics.com/pricing), you unlock advanced analytics:

- [Geo-location](https://www.kokoanalytics.com/features/geo-location/): See which countries your visitors come from.
- [Event Tracking](https://www.kokoanalytics.com/features/custom-event-tracking/): Track outbound link clicks, form submissions, and other custom events.
- [Email Reports](https://www.kokoanalytics.com/features/email-reports/): Get daily, weekly, or monthly summaries by email.
- [CSV Export](https://www.kokoanalytics.com/features/csv-export/): Export dashboard data for advanced analysis.
- [Pageviews Column](https://www.kokoanalytics.com/features/pageviews-column/): View pageviews directly in your WordPress admin post list.
- [Admin Bar](https://www.kokoanalytics.com/features/admin-bar/): See daily pageviews for a page right in the admin bar.
- [Traffic Spike Notifications](https://www.kokoanalytics.com/features/traffic-spike-notifications/): Get alerts when traffic surges.

Purchase at [kokoanalytics.com/pricing](https://www.kokoanalytics.com/pricing).


== Installation ==

You can install Koko Analytics in multiple ways:

1. In your WordPress admin, go to **Plugins > Add New**, search for **Koko Analytics**, and click **Install Now**.
2. [Download from WordPress.org](https://downloads.wordpress.org/plugin/koko-analytics.zip) and upload to `/wp-content/plugins/`.
3. [Download from GitHub](https://github.com/ibericode/koko-analytics/releases) and upload to `/wp-content/plugins/`.

Once activated, statistics will be collected immediately.

View your dashboard under **WP Admin > Dashboard > Analytics**.

== Frequently Asked Questions ==


#### Does this respect my visitor's privacy?
Yes. Koko Analytics only stores aggregated counts. No personal data is tracked.

#### Does this use any external services?
No. All data stays on your server.

#### Does Koko Analytics set any cookies?
By default yes, but you can disable cookies entirely in the settings.

If using cookies, Koko Analytics sets a single `_koko_analytics_pages_viewed` cookie (max 24h lifetime) to detect unique visitors without storing personal data.
More info: [Does Koko Analytics use cookies?](https://www.kokoanalytics.com/kb/does-koko-analytics-use-cookies/)

#### Will Koko Analytics slow down my website?
No. It’s built for speed:

- Only 500 bytes of JavaScript added.
- Data processing runs in the background.
- No extra DNS lookups — works with caching.

More info: [Will Koko Analytics slow down my website?](https://www.kokoanalytics.com/kb/will-koko-analytics-slow-down-my-website/)

#### Is the code open source?
Yes. [View on GitHub](https://github.com/ibericode/koko-analytics).

#### Where can I find documentation?
Visit the [Koko Analytics Knowledge Base](https://www.kokoanalytics.com/kb/).

#### How do I display visits for a page?
Use the `[koko_analytics_counter]` shortcode:

- `days`: Number of days (default `3650`).
- `global`: `true` for site-wide count, `false` for current page only.
- `metric`: `visitors` or `pageviews` (default `visitors`).

Example:
`[koko_analytics_counter days="30" metric="pageviews" global="true"]`

#### Why aren’t category/tag archives tracked?
Koko Analytics only tracks post/page views to keep storage requirements minimal.


== Screenshots ==

1. Dashboard view of your website statistics.
2. Dashboard widget with last 2 weeks of visits.
3. Widget showing most viewed posts for a time period.
4. Settings page with user role exclusion options.
5. Settings page with Pro features.
6. Pro feature showing pageviews in the posts overview.


== Changelog ==


### 1.8.6 - Jul 24, 2025

- Mention [geo-location](https://www.kokoanalytics.com/features/geo-location/), a new premium feature, in the settings page sidebar.
- Improved height of select and textarea elements on settings page.


### 1.8.5 - Jul 21, 2025

- Add new action hook to add table rows before the submit button on the settings page: `koko_analytics_extra_settings_rows_before_submit`
- Rewrite optimized endpoint to allow for filter and action hooks.
- Allow other plugins to filter pageview data in optimized endpoint.
- Allow other plugins to add PHP files to include in optimized endpoint.


### 1.8.4 - Jun 18, 2025

- Backwards compatibility with older versions of the tracking script. This is a special update for sites refusing to serve the latest version of the Koko Analytics script because of very stubborn caching plugins...


### 1.8.2 - Jun 13, 2025

- Fix cookieless tracking on PHP 8.0 and below.


### 1.8.1 - Jun 13, 2025

- Fix issue with optimized endpoint not working introduced in version 1.8.0


### 1.8.0 - Jun 12, 2025

- Added a new tracking method: [cookieless tracking](https://www.kokoanalytics.com/kb/cookie-vs-cookieless-tracking-methods).
- If using cookie-based tracking, the cookie lifetime has been changed to expire at midnight (so a maximum of 24 hours).
- Simplified client-side tracking script so it's now smaller than 500 bytes.
- Excluded IP addresses now work properly with the optimized endpoint.
- Added new dashboard date preset for "all time".
- Fixed chart issue where bars would be invisible if viewing a large amount of data on a small screen.


### 1.7.4 - May 14, 2025

- Use `wp_print_inline_script` function for printing inline script tags.
- Use `is_file` over `file_exists` for checking whether optimized endpoint file exists.
- Only remove optimized endpoint file if it fails verification if it was created by the plugin itself.


### 1.7.3 - Apr 15, 2025

- Explicitly add referrer to settings page form.
- Fix WP CLI command calling old method. Thanks [Oscar Arzola](https://github.com/arzola)!


### 1.7.2 - Mar 24, 2025

- Hide chart group options on dashboard widget chart.
- Run referrer URL's in dashboard widget through href normalizer.
- Very minor performance optimisation for generating chart HTML.


### 1.7.1 - Mar 10, 2025

- Fix chart bars being out of order for some databases in case of gaps in data.
- Include `manifest.json` file in plugin package.
- Check if uploads directory exists before calling `scandir`.
- Fix Query Loop Block from showing all posts in case of no stats.
- Address some W3C validation warnings for the dashboard HTML.
- Aggregate Reddit subdomains into a single referrer entry.


### 1.7.0 - Feb 25, 2025

- Compatibility with sites hosted on WP Engine (which does not allow writing files with the PHP extension to the WP Uploads directory).
- Use relative paths in optimized endpoint file, so that the site itself can be moved around freely on the filesystem.
- Run aggregator on `upgrader_process_complete` hook so that we can change aggregation logic without losing any data.
- Only use optimized endpoint if the file still exists and is verified to be working. This allows removing the file to immediately switch to the default AJAX endpoint.
- Fix a bug in the most viewed posts widget/shortcode that shows all posts instead of an empty result set.
- Add group by option to chart.
- Better align periods for comparisons.


### 1.6.6 - Feb 18, 2025

- Fix same-page referrer detection when not using cookie.
- Preserve page filter when using datepicker to change date period.
- Limit realtime pageviews in dashboard widget to today's pageviews when viewed shortly after midnight.


### 1.6.5 - Feb 10, 2025

- Add importer for data from Burst Statistics.
- Allow passing "false" and "no" to `monthly` query parameter in REST endpoints.
- Optimize PHP execution time for plugin bootstrapping code by aggressively lazy loading code that only runs on very specific requests. From 0.05 ms to 0.03 ms (!) with OPcache or 1.44 ms to 0.54 ms without OPcache for general requests.
- Minor code improvements to classes responsible for aggregating data from buffer file into permanent storage.


### 1.6.4 - Jan 25, 2025

- Fix dashboard data always publicly accessible through REST API endpoints.


### 1.6.3 - Jan 21, 2025

- Schedule missing `koko_analytics_aggregate_stats` event.
- Ensure pageview counts are always added to the correct day, even if WP Cron stalls for more than 24 hours.
- Improved correctness of the realtime visitor count in case of WP Cron stalls.
- Move the temporary buffer file to its own directory inside the uploads directory.
- Fix syntax error on PHP 7.4 introduced in version 1.6.2
- Jetpack Importer: Fix division by zero if importing a single day of data
- Jetpack Importer: Bump HTTP request timeout for Jetpack importer up from 5 seconds to 90 seconds.
- Jetpack Importer: Allow choosing a custom chunk size, which can be useful for sites with a large amount of posts or pages.
- Do not delete database tables on plugin uninstall by default.


### 1.6.1 - Jan 20, 2025

- Show error details in Jetpack Importer when API returns an error response (instead of only writing to error log).
- Register scheduled event for pruning data on plugin activation directly.
- Allow calling `get_realtime_pageview_count()` function with `null` parameter.


### 1.6.0 - Jan 17, 2025

- Bump minimal required PHP version to 7.4 or higher.
- Fix issue with date generation for "this_week" and "last_week" presets.
- Modify chart tooltip position so it's never causing horizontal overflow.
- Hide chart tooltip on scroll.
- Expand column width of pageviews and visitors column on large screens with ample space.
- Fix undefined array key notice introduced in version 1.5.5.
- Make all strings from Jetpack Importer feature translatable. Thanks to [Alex Lion](https://alexclassroom.com/).
- Fix REST API routes returning a HTTP 500 error if called without a `start_date` parameter.

PS. We've started the works on a [standalone version of Koko Analytics](https://github.com/koko-analytics/koko-analytics/) that allows you to track non-WordPress sites.

You can read some more about it on Danny's personal blog: https://www.dannyvankooten.com/blog/2025/building-privacy-friendly-website-analytics/


### 1.5.5 - Jan 10, 2025

Don't use `upgrader_process_complete` for checking pending database migration. This can't be used because this specific hook runs using the old version of the plugin...

Reverting this change from version 1.5.2 fixes an issue with the optimized endpoint file referencing an unexisting function.


#### 1.5.4 - Jan 10, 2025

- Fix optimized endpoint file referencing no-longer existing file on some installations.


#### 1.5.3 - Jan 09, 2025

- Add integration with Query Loop Block.
- Fix date range when viewing "this week" or "last week" on a Sunday.
- Remove non-functional settings example from [Koko Analytics Pro](https://www.kokoanalytics.com/pricing/) from settings page.
- Add one-time notice after at least 30 days of usage asking for a contribution.


#### 1.5.2 - Dec 17, 2024

- Improve logic for running pending database migrations.


#### 1.5.1 - Dec 10, 2024

- Fix date in chart tooltip using default PHP timezone, explicitly use site timezone instead.
- Check for excluded request (by IP address or user role) in unoptimized endpoint.
- Prevent PHP notice on dashboard if page URL does not have query component.


#### 1.5.0 - Nov 27, 2024

- Impose a maximum referrer URL length on data ingestion.
- Replace column header for visitors and pageviews with icon on small screens.
- Speed up `koko_analytics_counter` shortcode by having `Stats::get_total` not automatically pull in previous period.
- Migrations runner now updates the local database version after each individual step.
- Migrations runner now has a simple lock mechanism to ensure it runs atomically.
- Output database size in localized format.
- Output dates in localized format through `wp_date()`. Thanks to [Dominik Schilling](https://dominikschilling.de/).
- Add missing text domain on settings page. Thanks to [Dominik Schilling](https://dominikschilling.de/).


#### 1.4.5 - Nov 14, 2024

- Use localized number formatting for all numbers troughout the dashboard.
- Add feature to export and import data. Can only be used for sites with matching post ID's.
- Highlight weekends in chart by using a slightly darker color for the visitors part of the bar.


#### 1.4.4 - Nov 4, 2024

- Add Jetpack Stats importer to import your historical analytics data into Koko Analytics. Go to the settings page (with Jetpack still enabled) to access it.
- Fix settings page showing proxy IP instead of client IP if using reverse proxy.
- Fix use of PHP 7.4 only feature in thousands separator in source code.
- Auto-reload dashboard every minute if browser tab is active.
- Do not show chart for just a single day of data.
- Handle posts without title a little better by showing URL path instead.


#### 1.4.3 - Oct 29, 2024

- Fix "backtrack limit exhausted" triggering for certain referrer URL's without a subdomain part.
- Gracefully handle missing referrer blocklist file. This fixes an issue when security software on the server flags the blocklist file as suspicious (due to it containing a list of known malware domains) and deleting it.
- Increase width of first table column so rank isn't showing ellipsis.
- Remove light grey border on table header row.
- Right align numbers in dashboard widget. Thanks [Terence Eden](https://shkspr.mobi/blog/), who also did a wonderful post on [liberating your website statistics from Jetpack](https://shkspr.mobi/blog/2024/10/liberate-your-daily-statistics-from-jetpack/)!
- Improved validation of referrer URL's and request parameters at data collection endpoint.


#### 1.4.2 - Oct 25, 2024

- Fix fatal error "invalid string operand" when referrer URL contains `t.co` shortlink.
- Fix potential issue with `preg_match` returning incorrect type in function `get_referrer_url_label`


#### 1.4.1 - Oct 25, 2024

- Limit width of visitors and pageviews column in tables.
- Fix `preg_replace` from returning an invalid type when an error occurs and the log the actual error that occurred.


#### 1.4.0 - Oct 24, 2024

- All HTML for the dashboard is now server-side generated, drastically reducing the amount of JavaScript and generally making the code base easier to maintain and/or extend.
- All dashboard state can now be managed through URL query parameters, allowing you to bookmark or share your favorite views.
- Use actual `<table>` elements for dashboard tables, for improved screen reader support.
- Add filter hook `koko_analytics_referrer_url_href` to modify link `href` attribute for referrer URL's in dashboard.
- Add filter hook `koko_analytics_referrer_url_label` to modify link labels for referrer URL's in dashboard.
- Fix admin user not getting `view_koko_analytics` capability upon plugin activation.
- Prevent empty referrer URL from being stored.
- Group various Yandex referrer URL's into a single entry.
- Minor memory usage improvements in autoloader implementation.
