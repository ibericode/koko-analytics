=== Koko Analytics ===
Contributors: Ibericode, DvanKooten
Donate link: https://opencollective.com/koko-analytics
Tags: analytics, statistics, stats, koko
Requires at least: 5.0
Tested up to: 6.3
Stable tag: 1.0.40
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Requires PHP: 7.0

Privacy-friendly analytics for your WordPress site

== Description ==

Koko Analytics is a privacy-friendly analytics plugin for WordPress. It does not use any external services, so data about your visitors is never shared with any third-party company.

Furthermore, no visitor specific data is collected and visitors can easily opt-out of tracking by enabling "Do Not Track" in their browser settings.

Stop sharing visitor data with third-party companies making money off that same data. Stop unnecessarily slowing down your website. Koko Analytics lets you focus on what is important and gives you all the essential metrics, while respecting the privacy of your visitors.

### Features

- **Plug and play**: After installing and activating the plugin, stats will automatically be collected.
- **Privacy**: No personal information or anything visitor specific is tracked.
- **No external services**: Data about visits to your website is yours and yours alone.
- **Performance**: Handles thousands of simultaneous pageviews without breaking a sweat.
- **Lightweight**: Adds only 985 bytes of data to your pages.
- **GDPR**: Compliant by design.
- **Metrics**: All the essentials: visitors, pageviews and referrers.
- **Cookies**: There is an option to not use any cookies.
- **Referrer spam:** Built-in blacklist to filter out referrer spam.
- **Cache**: Fully compatible with pages served from any kind of cache.
- **AMP**: Tracks AMP powered pages too ([official AMP plugin](https://wordpress.org/plugins/amp/) only, for now).
- **Open-Source**: Code is released under the GPL 3.0 license.

### Contributing

You can contribute to Koko Analytics in many different ways. For example:

- Write about the plugin on your blog or share it on social media.
- [Vote on features in the GitHub issue list](https://github.com/ibericode/koko-analytics/issues?q=is%3Aopen+is%3Aissue+label%3A%22feature+suggestion%22).
- [Translate the plugin into your language](https://translate.wordpress.org/projects/wp-plugins/koko-analytics/stable/) using your WordPress.org account.
- Help fund the plugin through our [OpenCollective intiative](https://opencollective.com/koko-analytics).

== Installation ==

1. In your WordPress admin area, go to **Plugins > New Plugin**, search for **Koko Analytics** and click **Install now**.
1. Alternatively, [download the plugin files](https://downloads.wordpress.org/plugin/koko-analytics.trunk.zip) and upload the contents of `koko-analytics.zip` to your plugins directory, which usually is `/wp-content/plugins/`.
1. Activate the plugin. Koko Analytics will start recording stats right away.
1. Access your analytics by browsing to *Dashboard > Analytics* in your WordPress admin area.

== Frequently Asked Questions ==

#### Does this respect my visitor's privacy?
Absolutely, nothing that could lead back to the visitor is recorded. If the visitor has "Do Not Track" enabled in their browser settings, the visitor won't be tracked at all.

#### Does this use any external services?
No, the data never leaves your website. That's (part of) what makes Koko Analytics such a great choice if you value true privacy.

### Does this set any cookies?
By default, yes. But you can easily disable this in the plugin's settings. Without cookies the plugin can still detect unique pageviews, but not returning visitors.

### Will this slow down my website?
No, the plugin is built in such a way that it never slows down your website for your visitors. If there is any heavy lifting to be done, it is done in a background process.

In fact, because the plugin does not depend on any external services it is usually much faster than third-party analytics tools.

### No pageviews are being recorded.
This is usually a file permissions issue. The first thing to check is whether the `/wp-content/uploads/pageviews.php` file exists and is writable by your web server.

### How to only use cookies after consent?
First configure Koko Analytics to not use any cookies by default.

Then configure your cookie consent plugin to add the following code for visitors that have given consent:

`
<script>
if (window.koko_analytics) window.koko_analytics.use_cookie = true;
</script>
`

### How do I give users access to the dashboard page?
You can use a plugin like [User Role Editor](https://wordpress.org/plugins/user-role-editor/) to grant the `view_koko_analytics` and `manage_koko_analytics` capabilities to any user rolou can delete the plugin again after adding the capability.

### What is the definition of a "pageview"?
A pageview is defined as a view of a page on your site. If a user clicks reload after reaching the page, this is counted as an additional pageview. If a user navigates to a different page and then returns to the original page, a second pageview is recorded as well.

### What is the definition of a "visitor"?
A visitor represents the number of sessions during which your website or a specific page was viewed one or more times.

### I only see an empty page when viewing the analytics dashboard.
Koko Analytics was recently added to EasyPrivacy, a popular filter list which is used in many ad-blockers. It blocks both the tracking script and the dashboard script, but we are working with EasyPrivacy team to get this resolved.

Until then, please ensure to whitelist your own domain in your ad-blocker settings.

### How can I help fund Koko Analytics?
Please visit the [Koko Analytics project page on OpenCollective](https://opencollective.com/koko-analytics).


== Screenshots ==

1. Koko Analytics' dashboard to view your website statistics.
2. The settings page where you can exclude certain user roles from being counted.
3. A widget to show your most viewed posts (or any other post type) for a given period.
4. The dashboard widget to quickly show your site visits over the last 2 weeks.


== Changelog ==

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

- Print configuration object early on in page HTML so it is easier to override it using a cookie consent plugin.
- Add help text explaining the use of a multi-select element.


#### 1.0.9 - Mar 9, 2020

- Use arrow keys (without Ctrl-key) for quickly cycling through date ranges.
- Group chart by month if showing more than 2 (full) months of data.
- Replace React by Preact to cut JS bundle size in half.
- Normalize referrer URL's without protocol.
- Improve total comparision with previous period.


#### 1.0.8 - Feb 14, 2020

- Add date preset for last 28 days
- Add keyboard navigation support for quickly cycling through date periods (Ctrl + Arrow key)
- Expand referrer aggregation logic. If you have any URL's you would like to see combined into a single domain, please [post them here](https://github.com/ibericode/koko-analytics/issues/43).
- Fix referrer URL's table missing the AUTO_INCREMENT statement.
- Change dropdown to number input in most viewed posts widget.
- Show notice on dashboard page if an issue with WP Cron is detected.
- Improved y-axes in chart when maximum value is lower than 10.
- Use colors from admin scheme in chart tooltip.


#### 1.0.7 - Jan 30, 2020

- Aggregate certain referrers, e.g. google.com/search becomes google.com.
- Use WordPress color scheme (from user profile) for colors in chart.
- Show more labels on the chart's x-axes (wide screens only).
- Show number of pageviews in the last hour.
- Show day of week to chart tooltip.
- Use Paul Heckbert's loose labels (nice numbers) algorithm for labels on y-axes.
- All colors now have a contrast ratio that is (at least) WCAAG AA compliant.
- Revert multiple bar chart change (because of user feedback), use inner bar instead.
- Minor performance optimisations for viewing dashboard page.
- Fixes "Invalid time" error when re-opening the dashboard in Safari.


#### 1.0.6 - Jan 20, 2020

- Remember view period when navigating away from analytics dashboard.
- Add filter hook to prevent loading the tracking script: `koko_analytics_load_tracking_script`
- Ignore all user agents containing the word `seo`
- Ignore requests if page is loaded inside an iframe.
- Only read `document.cookie` if cookie use is actually enabled.
- In chart, use separate bars instead of stacked bars.


#### 1.0.5 - Dec 30, 2019

- Add "today" option to date periods preset menu.
- Hide chart component when viewing just a single day of data.
- Automatically refresh data in dashboard every minute.
- Use human readable number format on chart's y-axes.
- Show chart elements even if outside of chart container.


#### 1.0.4 - Dec 13, 2019

- Fix referrer URL's not being saved correctly.
- Fix unique pageview detection
- Fix pretty number with only trailing zeroes.
- Fix bar chart not stacking properly.
- Improved display of Twitter or Android app referrers.
- Improved chart tooltip.
- Improved styling for small mobile screens.
- Trim trailing slashes from referrer URL's.
- Escape all strings coming from translation files.
- Filter out common bots by checking user agent in tracking script.


#### 1.0.3 - Dec 6, 2019

- Fix link to settings page from plugins overview page.
- Fix REST API URL's when not using pretty permalinks.
- Add support for tracking AMP-powered pages.
- Add setting to disable cookie usage.
- Handle network request errors on admin pages.
- Return HTTP 500 error when unable to write to buffer file.
- Simplify adding post title to post type statistics.
- Extend browser support to include older browsers.
- Handle filesystem errors in aggregation process.


#### 1.0.2 - Nov 22, 2019

- Add icons to datepickers to quickly cycle through selected date periods.
- Add capabilities `view_koko_analytics` and `manage_koko_analytics` to control whether a user role can view or manage statistics.
- Add setting to automatically delete data older than X months.
- Add menu item to WP Admin Bar.
- Update URL when date range changes so page can be refreshed or shared.
- Update browser history with chosen date ranges.
- Show total size of Koko Analytics' database tables on settings page.
- Improved animations when dashboard data updates.
- Improved column type constraints for storing data.
- Improved labels for chart x-axes.
- Consistent ordering of posts and referrers tables.
- Remove trailing `?` character from referrer URL's after query parameters are stripped.
- Fix retrieving post title when post type is excluded from search.


#### 1.0.1 - Nov 14, 2019

- Add dashboard widget showing site visits over last 14 days.
- Add widget for showing most viewed posts, pages or any other post type over a given period.
- Add `[koko_analytics_most_viewed_posts]` shortcode.
- Add pagination to tables showing top posts and top referrers.
- Add settings link to plugin row on plugins overview page in WP admin.
- Use ASCII for storing textual data. Fixes an issue with error message "specified key is too long" on some MySQL installations when the charset is `utf8mb4`.
- Remove all data when uninstalling the plugin. Thanks to [Santiago Degetau](https://profiles.wordpress.org/tausworks/).
- Improved memory usage when handling huge bursts of traffic.
- Load tracking script asynchronously.
- Styling improvements for the dashboard page.


#### 1.0.0 - Nov 4, 2019

Initial release.

