=== Koko Analytics - Privacy+Friendly statistics for WordPress ===
Contributors: Ibericode, DvanKooten, hchouhan, lapzor
Tags: statistics, analytics, stats, google analytics, traffic
Requires at least: 6.0
Tested up to: 6.9
Stable tag: 2.1.1
License: GPL-3.0-or-later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Requires PHP: 7.4

Simple, plug & play statistics for WordPress. GDPR-compliant, privacy-friendly and self-hosted. Get effective analytics with Koko Analytics.

== Description ==

### Get privacy-friendly and easy to use statistics with Koko Analytics

Koko Analytics is a lightweight and privacy-friendly statistics plugin that runs entirely on your own WordPress site without requiring any third-party services.

It aims to be a simpler alternative to Google Analytics for WordPress sites.

Whether you want to see your most popular pages or understand where your visitors are coming from. Koko Analytics tracks all the essential metrics without compromising the privacy of your visitors or the performance of your site.

### Why Koko Analytics?

- **Plug and play** – Activate the plugin and statistics will start working right away.
- **Simple & effective** – No complicated pages to navigate, but a single page showing all that truly matters.
- **Privacy-friendly** – GDPR and CCPA compliant by design.
- **Own your data** – All data is processed and stored on your server only.
- **Fast and lightweight** – Adds less than 500 bytes of JavaScript and handles traffic spikes with ease.
- **No cookies needed** – Choose between cookie-based, cookieless, or no tracking at all.
- **Accurate essentials** – Counts visitors, unique pageviews, and referral URLs.

Koko Analytics provides you with effective and reliable website analytics without sharing visitor data with companies that also happen to sell advertisements.

You can [view a live demo here](https://www.kokoanalytics.com/koko-analytics-dashboard/).

This plugin is free, open-source and self-hosted — no account required. The [source code for Koko Analytics is available on GitHub here](https://github.com/ibericode/koko-analytics).

### Unlock advanced statistics with Koko Analytics Pro

You can benefit from several advanced features with [Koko Analytics Pro](https://www.kokoanalytics.com/pricing), like custom event tracking, counting visitor countries and periodic email reports.


== Installation ==

You can install Koko Analytics in multiple ways:

1. In your WordPress admin, go to **Plugins > Add New**, search for **Koko Analytics**, and click **Install Now**.
2. [Download from WordPress.org](https://downloads.wordpress.org/plugin/koko-analytics.zip) and upload to `/wp-content/plugins/`.
3. [Download from GitHub](https://github.com/ibericode/koko-analytics/releases) and upload to `/wp-content/plugins/`.

Once activated, statistics will be collected immediately.

View your dashboard under **WP Admin > Dashboard > Analytics**.

== Frequently Asked Questions ==

#### Is Koko Analytics privacy-friendly?

Yes.

- No personal data is processed or stored.
- Visitors are not tracked across pages or sites.
- Only aggregated counts are stored, nothing that could identify a single visitor.
- No third-party services are involved.

Read more about why [Koko Analytics is privacy-friendly](https://www.kokoanalytics.com/privacy-focused-wordpress-analytics/).

#### Is Koko Analytics lightweight? Will it slow down my site?

Koko Analytics is very fast.

- It only adds a single script of less than 500 bytes to your pages.
- Data is collected using an optimized tracking endpoint which bypasses loading WordPress entirely.

The performance impact for your visitors will be as close to zero as technically possible.

Read more about why [Koko Analytics is lightweight](https://www.kokoanalytics.com/lightweight-wordpress-analytics/).

#### Is Koko Analytics open-source?

Yes. [Koko Analytics is open-source software](https://www.kokoanalytics.com/open-source-wordpress-analytics/) released under the GPL license.

#### Does Koko Analytics set any cookies?

Yes, but you can disable cookies entirely in the settings.

If using cookies, Koko Analytics sets a single `_koko_analytics_pages_viewed` cookie (max 24h lifetime) to detect unique visitors without storing personal data.

More info: [Does Koko Analytics use cookies?](https://www.kokoanalytics.com/kb/does-koko-analytics-use-cookies/)

#### Where can I find more documentation?
On our [Koko Analytics Knowledge Base](https://www.kokoanalytics.com/kb/).

== Screenshots ==

1. Dashboard view of your website statistics.
2. Dashboard widget with last 2 weeks of visits.
3. Widget showing most viewed posts for a time period.
4. Settings page with user role exclusion options.
5. Settings page with Pro features.
6. Pro feature showing pageviews in the posts overview.


== Changelog ==


### 2.1.1 - Dec 24, 2025

- Use our own notice styles instead of the ones from WP core.
- Send Cache-Control header on public dashboard.
- Show some debug info on settings page.
- Ensure upload directory exists when creating session dir for fingerprint method.
- Add charset to collection endpoint HTTP response headers.
- Properly remove tooltip when mouse leaves the chart area.
- Move placeholder for tables without data outside of table element to fix column span issue.
- Add X-Robots-Tag: noindex to collection endpoint.
- Grey out table pagination when a page filter is active.
- Use fake hyperlinks for date navigation to stop bots from crawling public dashboards until infinity.


### 2.1.0 - Dec 08, 2025

- New settings page structure!
- Use existing removeable query args from WP core for notices.


### 2.0.22 - Dec 01, 2025

- specify apiVersion for block type so that WP 6.9 can use new iframe editor.


### 2.0.21 - Nov 28, 2025

- Prune blocked referrer domains retro-actively.
- Fix double echo on settings page.
- Fix hanging query on certain MySQL installations for deleting orphaned referrer rows.
- Add `wp koko-analytics prune` command for WP CLI.


### 2.0.20 - Nov 14, 2025

- Fix hard-coded table prefix in data export file.
- Increase batch size during data migration to v2 format from 500 to 1000 rows.
- Drop temporary table after data migration to v2.


### 2.0.19 - Oct 15, 2025

- Print (< 500 bytes) tracking script inline in page HTML to save on an additional HTTP request and resolve overly aggressive cache issues.
- Add importer for Plausible.
- Change public dashboard URL to `/koko-analytics-dashboard/` if pretty permalinks are enabled.
- Exclude visits to post previews.


### 2.0.18 - Sep 24, 2025

- Add filename alias for `Pageview_Aggregator` class so old autoloader knows where to find it. This fixes an error for users upgrading from 1.x with data in the temporary buffer file.
- Data importer now uses default WPDB connection and shows errors.
- Fallback to path if post title is empty or null.
- Replace section about Koko Analytics Pro with a smaller 'powered by ...' link for public dashboards.


### 2.0.17 - Sep 20, 2025

- Fix most viewed posts widget using old shortcode class name.


### 2.0.16 - Sep 19, 2025

- Prevent persistent object caches from breaking database migration lock mechanism.
- Add site URL to database export filename.
- Export table structure at the time of export.
- Database connection in importer now logs warnings instead of throwing an exception on database errors.


### 2.0.15 - Sep 17, 2025

- Fix count of total number of rows in table for grouped result.
- Fix compatibility with Borlabs Cookie library script.
- Add `koko_analytics_write_data_export` action hook.
- Show success/error message after certain user-initiated admin actions.
- Minor performance gain on dashboard.


### 2.0.14 - Sep 15, 2025

- Fix issue in v2 data migration for sites with over 500 distinct public posts.
- Fix pagination showing up despite there being no more items.
- Fix styling of file upload button on settings page.
- Disallow access to public dashboard for anything resembling a bot or crawler.


### 2.0.13 - Sep 11, 2025

- `post_id` column on `wp_koko_analytics_post_stats` table should be of type `INT`, not `MEDIUMINT`. This fixes an issue on sites with post ID's larger than 16777215.
- Fix datepicker dropdown heading spanning multiple lines on certain translations.


### 2.0.12 - Sep 09, 2025

- Fix incorrect post paths from data migration to v2.
- Fix table cell width on small screens for pageviews column.
- Add normalizer rule for AMP urls.
- Group on path_id to prevent duplicate paths for front-pages.
- Minor performance improvement in class autoloader.
- Run database migrations at `wp_loaded` hook to ensure all custom post types are registered.


### 2.0.11 - Aug 29, 2025

- Add script to correctly map post ID to path for sites where this went wrong.


### 2.0.10 - Aug 29, 2025

- Show page title again for records that have one.
- Fix the same path mapping to multiple entries in the `wp_koko_analytics_paths` table.
- Defend against stale client-side configuration object due to aggressive full-page caching.
- Drop database tables when using "reset statistics" button.


### 2.0.9 - Aug 28, 2025

- Automatically run the v2 data migration for tables with less than 25.000 total records.
- Pageviews column should be at least 6 characters wide to allow for 6-figure numbers.
- Improve performance of post stats migration script.
- Ship aggregator class on old filesystem location to prevent error from old autoloader.


### 2.0.8 - Aug 28, 2025

- Add WP CLI command for initiating post stats migration to v2: wp koko-analytics migrate_post_stats_to_v2
- Add WP CLI command for initiating referrer stats migration to v2: wp koko-analytics migrate_referrer_stats_to_v2
- Slightly decrease font-size for analytics ashboard in general and chart tooltip.
- Drop database tables on plugin uninstall.
- Truncate new paths table when resetting statistics.
- Switch to a single column grid at 1200px instead of 992px.


### 2.0.7 - Aug 28, 2025

- Run data migration at later hook so that custom post types have a chance to register.
- Delete seriously malformed referrer URL's from stats.
- Fix z-index of datepicker component.


### 2.0.5 - Aug 27, 2025

- Failsafe against missing referrer URL in buffer file.
- Fix warning about array to string conversion in wpdb class.
- Fix warning about foreach argument being null.
- Fix [koko_analytics_counter] shortcode not using path from post in "the loop" anymore.


### 2.0.3 - Aug 27, 2025

- Fix referrer migration notice re-appearing if certain records could not be migrated (due to being malformed).
- Fix table columns being pushed off the screen due to long text not truncating.
- Ensure post_stats primary key is properly created for new sites.


### 2.0.2 - Aug 27, 2025

- Delay running potentially expensive database migration until after site administrator clicks a button.


### 2.0.1 - Aug 27, 2025

This release introduces path based tracking and improved dashboard styles. If you have many different posts/pages or are concerned about your historical data being affected, please back-up your Koko Analytics database tables before updating to this version.

- With path based tracking, any page can be tracked. This includes category archives, search result pages and non-existing pages. The most viewed posts widget and shortcode are unaffected and retain their previous functionality.
- Improved dashboard CSS styling so that the standalone dashboard is now styled the same as the WordPress embedded dashboard.
- Only whitelisted referrer domains can include full page URL's.
- Referrers are not automatically hyperlinked anymore.
- Various other minor performance, security or code maintenance improvements.


#### 1.8.6 - Jul 24, 2025

- Mention [geo-location](https://www.kokoanalytics.com/features/geo-location/), a new premium feature, in the settings page sidebar.
- Improved height of select and textarea elements on settings page.


#### 1.8.5 - Jul 21, 2025

- Add new action hook to add table rows before the submit button on the settings page: `koko_analytics_extra_settings_rows_before_submit`
- Rewrite optimized endpoint to allow for filter and action hooks.
- Allow other plugins to filter pageview data in optimized endpoint.
- Allow other plugins to add PHP files to include in optimized endpoint.


#### 1.8.4 - Jun 18, 2025

- Backwards compatibility with older versions of the tracking script. This is a special update for sites refusing to serve the latest version of the Koko Analytics script because of very stubborn caching plugins...


#### 1.8.2 - Jun 13, 2025

- Fix cookieless tracking on PHP 8.0 and below.


#### 1.8.1 - Jun 13, 2025

- Fix issue with optimized endpoint not working introduced in version 1.8.0


#### 1.8.0 - Jun 12, 2025

- Added a new tracking method: [cookieless tracking](https://www.kokoanalytics.com/kb/cookie-vs-cookieless-tracking-methods).
- If using cookie-based tracking, the cookie lifetime has been changed to expire at midnight (so a maximum of 24 hours).
- Simplified client-side tracking script so it's now smaller than 500 bytes.
- Excluded IP addresses now work properly with the optimized endpoint.
- Added new dashboard date preset for "all time".
- Fixed chart issue where bars would be invisible if viewing a large amount of data on a small screen.


#### 1.7.4 - May 14, 2025

- Use `wp_print_inline_script` function for printing inline script tags.
- Use `is_file` over `file_exists` for checking whether optimized endpoint file exists.
- Only remove optimized endpoint file if it fails verification if it was created by the plugin itself.


#### 1.7.3 - Apr 15, 2025

- Explicitly add referrer to settings page form.
- Fix WP CLI command calling old method. Thanks [Oscar Arzola](https://github.com/arzola)!


#### 1.7.2 - Mar 24, 2025

- Hide chart group options on dashboard widget chart.
- Run referrer URL's in dashboard widget through href normalizer.
- Very minor performance optimisation for generating chart HTML.


#### 1.7.1 - Mar 10, 2025

- Fix chart bars being out of order for some databases in case of gaps in data.
- Include `manifest.json` file in plugin package.
- Check if uploads directory exists before calling `scandir`.
- Fix Query Loop Block from showing all posts in case of no stats.
- Address some W3C validation warnings for the dashboard HTML.
- Aggregate Reddit subdomains into a single referrer entry.


#### 1.7.0 - Feb 25, 2025

- Compatibility with sites hosted on WP Engine (which does not allow writing files with the PHP extension to the WP Uploads directory).
- Use relative paths in optimized endpoint file, so that the site itself can be moved around freely on the filesystem.
- Run aggregator on `upgrader_process_complete` hook so that we can change aggregation logic without losing any data.
- Only use optimized endpoint if the file still exists and is verified to be working. This allows removing the file to immediately switch to the default AJAX endpoint.
- Fix a bug in the most viewed posts widget/shortcode that shows all posts instead of an empty result set.
- Add group by option to chart.
- Better align periods for comparisons.


#### 1.6.6 - Feb 18, 2025

- Fix same-page referrer detection when not using cookie.
- Preserve page filter when using datepicker to change date period.
- Limit realtime pageviews in dashboard widget to today's pageviews when viewed shortly after midnight.


#### 1.6.5 - Feb 10, 2025

- Add importer for data from Burst Statistics.
- Allow passing "false" and "no" to `monthly` query parameter in REST endpoints.
- Optimize PHP execution time for plugin bootstrapping code by aggressively lazy loading code that only runs on very specific requests. From 0.05 ms to 0.03 ms (!) with OPcache or 1.44 ms to 0.54 ms without OPcache for general requests.
- Minor code improvements to classes responsible for aggregating data from buffer file into permanent storage.


#### 1.6.4 - Jan 25, 2025

- Fix dashboard data always publicly accessible through REST API endpoints.


#### 1.6.3 - Jan 21, 2025

- Schedule missing `koko_analytics_aggregate_stats` event.
- Ensure pageview counts are always added to the correct day, even if WP Cron stalls for more than 24 hours.
- Improved correctness of the realtime visitor count in case of WP Cron stalls.
- Move the temporary buffer file to its own directory inside the uploads directory.
- Fix syntax error on PHP 7.4 introduced in version 1.6.2
- Jetpack Importer: Fix division by zero if importing a single day of data
- Jetpack Importer: Bump HTTP request timeout for Jetpack importer up from 5 seconds to 90 seconds.
- Jetpack Importer: Allow choosing a custom chunk size, which can be useful for sites with a large amount of posts or pages.
- Do not delete database tables on plugin uninstall by default.


#### 1.6.1 - Jan 20, 2025

- Show error details in Jetpack Importer when API returns an error response (instead of only writing to error log).
- Register scheduled event for pruning data on plugin activation directly.
- Allow calling `get_realtime_pageview_count()` function with `null` parameter.


#### 1.6.0 - Jan 17, 2025

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


#### 1.5.5 - Jan 10, 2025

Don't use `upgrader_process_complete` for checking pending database migration. This can't be used because this specific hook runs using the old version of the plugin...

Reverting this change from version 1.5.2 fixes an issue with the optimized endpoint file referencing an unexisting function.


#### 1.5.4 - Jan 10, 2025

- Fix optimized endpoint file referencing no-longer existing file on some installations.


#### 1.5.3 - Jan 09, 2025

- Add integration with Query Loop Block.
- Fix date range when viewing "this week" or "last week" on a Sunday.
- Remove non-functional settings example from [Koko Analytics Pro](https://www.kokoanalytics.com/pricing/) from settings page.
- Add one-time notice after at least 30 ...

