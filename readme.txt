=== Koko Analytics - Privacy-Friendly Statistics for WordPress ===
Contributors: Ibericode, DvanKooten
Donate link: https://wordpress.org/support/plugin/koko-analytics/reviews/#new-post
Tags: analytics, statistics, stats, google analytics
Requires at least: 6.0
Tested up to: 6.8
Stable tag: 1.8.6
License: GPL-3.0-or-later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Requires PHP: 7.4

Get plug and play, privacy-friendly and GDPR/CCPA compliant statistics for WordPress with Koko Analytics.

== Description ==

= Plug & Play Privacy-Friendly Analytics for WordPress =

Koko Analytics is a privacy-friendly analytics plugin for WordPress sites.

It does not use any external services, so no data is shared with any third-party. Nothing specific to any individual visitor is collected, only aggregated counts.

Stop sharing visitor data with third-party companies who also happen to sell ads (looking at you, Google Analytics). Stop slowing down your website with large tracking scripts. Liberate and own your data!

Koko Analytics lets you focus on the important statistics while completely respecting the privacy of your visitors.

You can view a [live demo here](https://www.kokoanalytics.com/?koko-analytics-dashboard).

**Koko Analytics is a free plugin that does not require an account with a third-party service. It runs completely on your own server.**

### Features

- **Plug and play**: After installing and activating the plugin, statistics will automatically be collected.
- **No external services**: Any data never leaves your server.
- **No personal data** or anything visitor specific is tracked.
- **No cookies**: You can choose which tracking method to use; cookie-based, cookieless or none at all.
- **Fast**: Handles hundreds of concurrent pageviews without breaking a sweat.
- **Lightweight**: Only 500 bytes (!) of JavaScript is added to your pages. In your database, a full year worth of data will take up less than 10 MB of storage.
- **GDPR Compliant** by design.
- **Metrics**: All the essentials: total pageviews, unique pageviews and referral URL's.
- **Referrer spam:** Built-in blocklist to filter out referrer spam.
- **REST API**: You can access your data from anywhere via REST API endpoints.
- **Cached**: Fully compatible with pages served from any kind of cache.
- **Open-Source**: The plugin code is [open-sourced](https://github.com/ibericode/koko-analytics) under the GPL-3.0-or-later license.
- **Tested**: Ready for PHP 8.4, but compatible down to PHP 7.3.
- **AMP**: Tracks AMP powered pages too ([official AMP plugin](https://wordpress.org/plugins/amp/) only).
- **Import data from other plugins**: You can import historical data from Jetpack Stats or Burst Statistics.


### Upgrade to Koko Analytics Pro

All of the features listed above are available for free. Some advanced features are available through [Koko Analytics Pro](https://www.kokoanalytics.com/pricing):

- [Geo-location](https://www.kokoanalytics.com/features/geo-location/): see exactly which countries your site is visited from.
- [Event Tracking](https://www.kokoanalytics.com/features/custom-event-tracking/): track outbound link clicks, form submissions or any other type of custom event.
- [Email Reports](https://www.kokoanalytics.com/features/email-reports/): Receive a daily, weekly or monthly email summary of your website's most important statistics.
- [CSV Export](https://www.kokoanalytics.com/features/csv-export/): The ability to export your dashboard data to CSV for advanced analysis.
- [Pageviews Column](https://www.kokoanalytics.com/features/pageviews-column/): Shows the number of pageviews over a configurable time period right in your WP Admin posts and pages overview.
- [Admin Bar](https://www.kokoanalytics.com/features/admin-bar/): Shows daily pageviews for a specific page in your WordPress Admin Bar.
- [Traffic Spike Notifications](https://www.kokoanalytics.com/features/traffic-spike-notifications/): receive an email notification when your website experiences a traffic spike.

You can [purchase Koko Analytics Pro here](https://www.kokoanalytics.com/pricing/).


== Installation ==

You can install Koko Analytics in multiple ways:

- In your WordPress admin area, go to **Plugins > New Plugin**, search for **Koko Analytics** and click **Install now**.
- [Download the latest version from WordPress.org](https://downloads.wordpress.org/plugin/koko-analytics.zip) and extract the files into your `/wp-content/plugins/` directory.
- [Download the latest release from GitHub](https://github.com/ibericode/koko-analytics/releases) and extract the files into your `/wp-content/plugins/` directory.

After installing and then activating the plugin, statistics will be collected right away.

You can view your dashboard by going to **WP Admin > Dashboard > Analytics**.


== Frequently Asked Questions ==

#### Does this respect my visitor's privacy?
Yes, absolutely. Koko Analytics only stores aggregated counts. Nothing visitor specific is tracked.

#### Does this use any external services?
No.

### Does Koko Analytics set any cookies?
By default yes, but you can disable the use of cookies entirely from the plugin's settings page by choosing a different tracking method.

If using cookie-based tracking, Koko Analytics sets a single cookie named `_koko_analytics_pages_viewed` with a lifetime of at most `24 hours`. This cookie is used to accurately detect unique pageviews and returning visitors without having to store any personal information on your server.

[https://www.kokoanalytics.com/kb/does-koko-analytics-use-cookies/](https://www.kokoanalytics.com/kb/does-koko-analytics-use-cookies/)

### Will Koko Analytics slow down my website?
No, the plugin is built in such a way that it never slows down your website for your visitors.

- It only adds a single script of less than 500 bytes to your pages.
- All data processing is done in a separate background process.
- Everything lives on your server, so the plugin doesn't add any additional DNS look-ups and can benefit from your server's cache policy.

[https://www.kokoanalytics.com/kb/will-koko-analytics-slow-down-my-website/](https://www.kokoanalytics.com/kb/will-koko-analytics-slow-down-my-website/)

### Is the code for this plugin on GitHub?
Yes, see [github.com/ibericode/koko-analytics](https://github.com/ibericode/koko-analytics).

### Where can I find more documentation?
Have a look at the [Koko Analytics knowledge base](https://www.kokoanalytics.com/kb/).

### How to show number of visits to a page?
You can use the `[koko_analytics_counter]` shortcode to show the number of visitors to the current page.

It takes 3 optional arguments:

- `days`: Show count over the last N days. Defaults to "3650".
- `global`: Whether to show the global count (for the entire site) or for the current page only. Defaults to "false".
- `metric`: One of "visitors" or "pageviews". Defaults to "visitors".

Example use with arguments:

`
[koko_analytics_counter days="30" metric="pageviews" global="true"]
`

### Why are category and tag archives not tracked?
Koko Analytics is currently only able to track posts, pages and other post types. This is so that Koko Analytics does not have to store URL's or post titles, which would take up a lot of storage space.


== Screenshots ==

1. Koko Analytics' dashboard to view your website statistics.
2. The dashboard widget to quickly show your site visits over the last 2 weeks.
3. A widget to show your most viewed posts (or any other post type) for a given period.
4. The settings page where you can exclude certain user roles from being counted.
5. Screenshot of settings page showing some features from Koko Analytics Pro.
6. Koko Analytics Pro can show your pageviews over a configurable time period right in your posts overview.


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


#### 1.3.15 - Oct 15, 2024

- Fix `href` attribute on hyperlinks in most viewed posts widget/shortcode/function template.


#### 1.3.14 - Sep 23, 2024

- Explicitly call `sprintf` from global namespace to benefit from upcoming sprintf related performance improvements in PHP 8.4.
- Demo settings from [Koko Analytics Pro](https://www.kokoanalytics.com/pricing/) on plugin settings page.


#### 1.3.13 - Sep 17, 2024

- Ensure `Stats::get_totals` always returns a valid object.
- Escape return values from `add_query_arg` to prevent reflected XSS attacks.
- Use correct IP address even if client is behind proxy.
- Various minor template performance improvements.


#### 1.3.12 - Aug 18, 2024

- Fix double pageview counts introduced in version 1.3.11.
- Fix same-site showing up as referrer


#### 1.3.11 - Aug 16, 2024

- Only use referrer detection for determining returning visitors if cookie is disabled.
- Add referrer aggregation rule for Brevo email campaign links.
- Add referrer aggregation rule for Reddit links.
- Add filter hook for easily adding or modifying Koko Analytics settings.
- Add action hook for adding settings to Koko Analytics.
- Explicitly get rid of all ES6 code in tracking script.


#### 1.3.10 - Jun 20, 2024

- Registration for [Koko Analytics Pro](https://www.kokoanalytics.com/pricing/) is open again. Purchase a license if you need custom event tracking or would just like to support the plugin.
- Ignore requests from Facebook link previews and requests without a `User-Agent` HTTP header.
- Update referrer blocklist.


#### 1.3.9 - May 31, 2024

- Fix Webpack issue with tracking script.


#### 1.3.8 - May 29, 2024

- Add setting to exclude views from IP addresses.
- Show exact number of pageviews and visitors on hover.
- Use an optimized custom autoloader.
- Verify shortcode arguments for `[koko_analytics_counter]` shortcode.
- Fix error when using SQLite about ambiguous column name.
- Fix realtime pageview count using wrong duration.


#### 1.3.7 - Feb 26, 2024

- Add `[koko_analytics_counter]` shortcode. Thanks Anil Kulkarni!
- Show time since last aggregation on settings page.
- Validate data collection request more aggressively before writing to buffer file.
- Update referrer blocklist.


#### 1.3.6 - Jan 29, 2024

- Update referrer blocklist.
- Update third-party JS dependencies.


#### 1.3.5 - Jan 8, 2024

- Fix `HOUR_IN_SECONDS` constant not defined when using AMP with cookie enabled.
- Fix days without any data not showing up in chart.
- Improve chart y-axes for numbers just above 100.000.


#### 1.3.4 - Nov 21, 2023

- New feature that allows you to filter by page. Clicking any page in the "top pages" list now updates the totals and chart component to only show visitors and pageviews for that specific page.
- Fix warning that cron event isn't working not showing.
- Fix error when default date period is stuck at removed period.
- Fix API url for sites not using pretty permalinks.
- Performance improvement for rendering chart and tooltips.


#### 1.3.3 - Nov 6, 2023

- Fix quick navigation going forward.
- Add `manifest.json` file so (standalone) dashboard can be installed as a Progressive Web App.


#### 1.3.2 - Nov 2, 2023

- Fix chart tooltip immediately disappearing when viewing the dashboard widget on touch devices.
- Fix fatal error if lacking permissions to read database size from MySQL information tables.
- Fix double encoding of special characters in post titles.
- Fix arrow-key or arrow-icon navigation when viewing a single day of data.
- Automatically refresh dashboard data every 60s.
- Improve dashboard widget by showing a summary of today.
- Added filter hook `koko_analytics_dashboard_widget_number_of_top_items` to [modify or disable the top pages and referrers in the dashboard widget](https://github.com/ibericode/koko-analytics/blob/master/code-snippets/modify-dashboard-widget/README.md).
- The `[koko_analytics_most_viewed_posts]` shortcode now shows a debug message if the arguments did not lead to any results.


#### 1.3.1 - Nov 1, 2023

- Fix new visitors not being counted.
- Fix dashboard issues for users with a large UTC timezone offset.
- Fix date format in chart component if grouping by month.
- Revert to blue colors for the chart. If you want your chart to use different colors, please see this [example code snippet on how to change colors](https://github.com/ibericode/koko-analytics/blob/master/code-snippets/change-chart-colors.php).
- Change dashboard widget to show just a quick summary of today.
- Show some feedback after using the "create optimized endpoint" button.


#### 1.3.0 - Oct 31, 2023

- Major [performance improvements for the dashboard](https://www.kokoanalytics.com/2023/10/31/speeding-up-dashboard-removing-react-vanilla-js/) by removing the dependency on React and further optimizations.
- Added link for loading the dashboard outside of WordPress admin (standalone).
- Added setting to make the analytics dashboard publicly available.
- Only show button to create optimized endpoint file if location is writable.
- Remove `wp_koko_analytics_dates` on plugin uninstall.
- Optimized database query for getting most viewed posts and cache its results.
- Expand dashboard widget to include realtime pageviews and a list of the most viewed posts.
- Use `navigator.sendBeacon` for data collection requests.


#### 1.2.2 - Oct 18, 2023

- Fix link to settings page from plugins overview page.
- Fix pagination not working because `wp_localize_script` turns everything into a string.
- Fix React warning for dashboard widget when toggling visibility more than once.
- Fix chart tooltip not showing on touch devices.
- Minor styling improvements to settings page.
- Install custom endpoint on plugin activation only.
- Add button to settings page to re-attempt custom endpoint installation.
- Move table pagination to bottom of component and add text label.
- Wrap `input[type="radio"]` in `<fieldset>` tag.
- Remove support for honouring "Do Not Track" header as per [MDN recommendations](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/DNT).


#### 1.2.1 - Oct 11, 2023

- Fix issue with strict types and non-hourly UTC offsets.


#### 1.2.0 - Oct 11, 2023

- Fix compatibility with WordPress 6.0.
- Fix aggregation process not running for 5 minutes if an earlier run failed somehow.
- Fix chart tooltips not showing on WordPress dashboard.
- Minor performance improvements for aggregation process.
- Move seed (sample data) function out of the core plugin.
- Add message about checking browser console for error message in case the dashboard doesn't boot up.
- Format dates in dashboard using `Intl.DateTimeFormat` in browser, if available.
- Remove Pikaday datepicker in favor of native `<input type="date">` elements.
- Improve mobile view of datepicker dropdown.
- Exclude (fixed page) homepage from most viewed posts widget/shortcode/function.
- Add filter hook `koko_analytics_items_per_page` to override the number of items to show per page for the dashboard components.
- Bump required PHP version to 7.3.


#### 1.1.2 - Oct 3, 2023

- Fix broken totals and chart component on sites using a custom database table prefix.


#### 1.1.1 - Oct 3, 2023

- Fix for date table not being created, leading to an empty chart as of v1.1.0.


#### Koko Analytics v1.1.0 - Oct 3, 2023

- Switch out Preact for the React version that is bundled with WordPress, reducing bundle size for the admin dashboard by 40 kB (or 30%).
- Stop showing warning about WP Cron events not running if on local or developer environments.
- Use the same Browserslist configuration as WordPress core.
- Performance optimizations for fetching and parsing chart data.
- Create optimized endpoint for fetching data for the totals component.
- Settings page is now a server-side rendered page instead of a React component.
- Improved CSS selector performance.
- Add public PHP API. You can now call the following functions:
	- `koko_analytics_get_most_viewed_posts()` to get a list of the most viewed posts.
	- `koko_analytics_get_realtime_pageview_count('-1 hour')` to get the total number of pageviews in the last hour.
	- `koko_analytics_track_pageview($post_id)` to track a pageview to the post with ID `$post_id`


#### 1.0.40 - Sep 14, 2023

- Fallback to post slug if post has no title
- Validate referrer URL and ignore if invalid
- Delete optimized tracking endpoint if buffer filename changed and is no longer present in it. This fixes an issue when moving between servers
- Always run database migrations when needed, regardless of current user role
- Allow specifying multiple post types in `KokoAnalytics\get_most_viewed_posts()` and the `[koko_analytics_most_viewed_posts]` shortcode. Example: `[koko_analytics_most_viewed_posts post_type="page,post"]`
- Limit attempts to install optimized tracking endpoint to once per hour
- On the analytics dashboard, use the date format from WordPress settings
- Translate day and month names (only relevant if using M, F, l or D in the date format string)
- WP CLI command to manually run aggregation now accepts an optional `--force` flag. Example: `wp koko-analytics aggregate --force`
- Don't show warning about WP Cron not working properly on sites on localhost, sites ending in `.local` and sites containing a port number
- Last but certainly not least, some preparatory work for event tracking... Coming soon!


### 1.0.39 - Aug 29, 2023

- Update referrer blocklist
- Update third-party JS dependencies
- Bump tested WordPress version


### 1.0.38 - Apr 25, 2023

- Fix issue with dashboard widget rendering incorrectly when it was initially hidden.
- Only render chart in dashboard widget when it is currently visible.


### 1.0.37 - Dec 07, 2022

- Fix potential issue in tracking script causing incorrect stats collection.


### 1.0.36 - Dec 06, 2022

- Shrink size of tracking script to 985 bytes (when using gzip compression).


### 1.0.35 - Oct 21, 2022

- You can now call `koko_analytics.trackPageview( postId )` to manually track a pageview. This is especially useful for Single Page Applications.
- Update referrer blocklist.
- If referrer URL is on referrer blocklist, the entire request is now ignored (vs. just the referrer part).
- Update JavaScript dependencies.


#### 1.0.34 - Sep 19, 2022

- Track visits to front page (if not a static page) as well.
- Use gmdate() for determining whether to send cache headers.
- Update JavaScript dependencies.


#### 1.0.33 - Aug 17, 2022

- Always show at least 1 visitor if pageviews were recorded that day.
- Decrease Cache-Control header of REST API responses to 60s.
- Add button to reset all statistics.
- Add filter `koko_analytics_url_aggregations` to modify or add URL aggregations for referrer URL's.
- Created an initiative on OpenCollective for others to help fund development and support costs of Koko Analytics. Please visit https://opencollective.com/koko-analytics for more details.


#### 1.0.32 - Jun 2, 2022

- Fix error in latest release by ensuring Endpoint_Installer class is always loaded when needed.


#### 1.0.30 - Jun 1, 2022

- Delete option `koko_analytics_use_custom_endpoint` on plugin uninstall.
- Only load dashboard-widget.js if user has required capability for viewing stats.
- Add constant `KOKO_ANALYTICS_CUSTOM_ENDPOINT` for  [disabling the custom endpoint](https://github.com/ibericode/koko-analytics/blob/master/code-snippets/disable-custom-endpoint.php) entirely. This filter can also be used to [manually install the endpoint file to a different file location](https://github.com/ibericode/koko-analytics/blob/master/code-snippets/use-different-custom-endpoint.php).
- Re-attempt installation of the custom endpoint file every hour. This also automatically re-tests whether the custom endpoint is still working as expected. The plugin already did this whenever you visited the dashboard page, but now it simply runs more often and without requiring you to log-in to your WordPress admin.


#### 1.0.29 - Jan 25, 2022

- Skip empty lines in buffer file to avoid a PHP notice in certain edge cases.
- Make use of JS library for date presets.
- Filter out user agents containing "preview".
- Normalize referrer URl's for Yahoo search results.
- Add class attribute to cron warning so it can be hidden using CSS by targeting `.koko-analytics-cron-warning`.


#### 1.0.28 - Sep 27, 2021

- Account for missing configuration object, for example if theme does not properly call `wp_head()`.
- Cast database result to integer types so we do not have to do it client-side.
- Cache dashboard requests to REST API for 5 minutes (using browser cache).
- Use integers for viewbox coordinates (for increased performance).
- Simple locking mechanism for aggregation job to detect previous runs which are still busy.
- Add WP CLI command for aggregating stats without having to go through WP Cron: `wp koko-analytics aggregate`


#### 1.0.27 - May 4, 2021

- Use `defer` attribute on script to not block parsing at all.
- Normalize Pinterest URL's with and without www subdomain.
- Fix some missing translations.


#### 1.0.26 - Feb 17, 2021

- Re-introduce last 28 days by popular request.
- Fix date presets dropdown not working in WebKit based browsers.
- Update built-in referrer blocklist.


#### 1.0.25 - Feb 16, 2021

- Add several new date presets, like yesterday or last week.
- Add support for entering custom date ranges using keyboard input.
- Improve algorithm for chart y-axes to prevent huge jumps.
- Add function for manually printing tracking script: `<?php koko_analyics_tracking_script(); ?>`
- Update JS dependencies.


#### 1.0.24 - Jan 20, 2021

- Append random query parameter to tracking request to avoid beind cached now that the plugin is using a custom tracking endpoint.
- Update JS dependencies.


#### 1.0.23 - Jan 15, 2021

Major performance improvement by writing an optimized endpoint file containing the correct file paths, regardless of WordPress directory structure set-up.

If your WordPress root directory is not writable, go to the Koko Analytics settings page for instructions on how to manually create this optimized endpoint file.


#### 1.0.22 - Nov 18, 2020

- Fix PHP warnings in migration script, for trying to unlink unexisting file  (eg on new plugin installs).
- Generate URL to custom endpoint file correctly. Fixes issue with some multilingual plugins not recording pageviews correctly.


#### 1.0.21 - Nov 5, 2020

- Update option that stores whether to use custom endpoint after unlinking it, to prevent sending requests to an unexisting file.


#### 1.0.20 - Nov 3, 20202

- Do not use custom endpoint file if using custom uploads directory.
- Use value from `KOKO_ANALYTICS_USE_CUSTOM_ENDPOINT` if it is defined.
- Only call add_cap on administrator role if such a role exists
- Update JS dependencies.
- Update built-in referrer blocklist.


#### 1.0.19 - Sep 2, 2020

- Create buffer file directory if it does not exist yet, eg on a fresh WP install.
- Update preact and date-fns to their latest versions.
- Update built-in referrer blocklist.


#### 1.0.18 - Aug 25, 2020

- Fix issue with tracking not working on AMP powered pages or issuing a request to a non-existing file on cdn.ampproject.org.


#### 1.0.17 - Aug 19, 2020

- Fix issue when using Modern color scheme introduced in WordPress 5.5.
- Improve test for custom endpoint file by checking for exact response body.
- Prevent horizontal scrollbar from showing when hovering chart near edge of screen.


#### 1.0.16 - Jul 21, 2020

- Fix blank screen on WordPress versions lower than 5.0.
- Fix settings link showing despite user lacking required capability.
- Fix code for custom referrer blocklist using filter `koko_analytics_referrer_blocklist`.
- Do not use custom endpoint file when site URL differs from WordPress URL.
- Improve test for custom endpoint file.


#### 1.0.15 - Jun 22, 2020

- Fix weird date for "this week" preset.
- Fix weeknames in datepicker.
- Fix translation files not being loaded. Hopefully...


#### 1.0.14 - Jun 22, 2020

- Do not use custom tracking endpoint on Multisite installations because it ignores the site-specifix database prefix.
- Show errors in aggegration process if `WP_DEBUG` is enabled.
- Update referrer blocklist.
- Use `wp.i18n` for managing translations in JavaScript files.
- Bump table row count up to 25 per page.
- Add filter hook: `koko_analytics_referrer_blocklist` ([example](https://github.com/ibericode/koko-analytics/blob/master/code-snippets/add-domains-to-referrer-blocklist.php))
- Add filter hook: `koko_analytics_ignore_referrer_url` ([example](https://github.com/ibericode/koko-analytics/blob/master/code-snippets/ignore-some-referrer-urls.php))


#### 1.0.13 - May 28, 2020

- Update referrer blocklist.
- Improve date parsing from URL parameters to account for negative UTC offsets. Fixes an issue with the date jumping back one day.
- Don't attempt to install custom endpoint if it was manually installed (using the `KOKO_ANALYTICS_USE_CUSTOM_ENDPOINT` constant).
- Revert to using `home_url()` for the tracker endpoint URL.


#### 1.0.12 - May 14, 2020

- Add filter `koko_analytics_honor_dnt` to allow ignoring DoNotTrack.
- Huge performance improvement for the tracking request if you're on a standard WordPress installation with the root directory writable.
- Limit scope of tracking script to prevent variable naming collisions.


#### 1.0.11 - Apr 17, 2020

- Add setting for specifying default date period when opening analytics dashboard.
- Add Chrome-Lighthouse to list of ignored HTTP user agents.
- Show notice on analytics dashboard page when buffer file is not writable.
- Derive cookie path from home URL to work properly with WordPress installations not living at the root of a domain.
- Track pageview on `window.load` instead of `window.DOMContentLoaded`, to make it easier to overwrite the configuration object.
- Minor optimizations to tracking script.


#### 1.0.10 - Mar 23, 2020

- Print configuration object early on in page HTML so it is easier to override it using ...

