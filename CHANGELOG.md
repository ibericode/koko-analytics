# Changelog

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
