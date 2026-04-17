=== Koko Analytics - Privacy Friendly Statistics for WordPress ===
Contributors: Ibericode, DvanKooten, kokoanalytics
Tags: analytics, google analytics, statistics, stats, privacy
Requires at least: 6.5
Tested up to: 6.9.4
Stable tag: 2.3.3
License: GPL-3.0-or-later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Requires PHP: 7.4

Koko Analytics is a privacy-friendly statistics plugin for WordPress that is an easy to use alternative to Google Analytics.

== Description ==

Koko Analytics provides website analytics and visitor statistics directly inside your WordPress dashboard without relying on external services. It is privacy-friendly, lightweight, open source, and easy to use.

Fully GDPR, CCPA and PECR compliant by design: no personal data is processed or stored, everything runs on your own server and can be used without cookies.

You can [view a live demo here](https://www.kokoanalytics.com/koko-analytics-dashboard/).

### Why Koko Analytics

Our goal is to provide you with a simple, lightweight and privacy-friendly alternative to Google Analytics for your WordPress statistics.

#### Privacy Friendly Analytics

Koko Analytics is [privacy friendly analytics](https://www.kokoanalytics.com/privacy-focused-wordpress-analytics/). No personal data is processed or stored, all measurements are carried out completely anonymously and nothing is ever shared with any third-party service. 

#### Lightweight Statistics

Koko Analytics is [lightweight analytics](https://www.kokoanalytics.com/lightweight-wordpress-analytics/). It adds less than 1 kilobyte of data to your HTML and is fully compatible with pages served from any kind of cache. WordPress is bypassed entirely for its collection endpoint, making the impact on your site's performance as close to zero as possible. Fact: there is no faster statistics plugin for WordPress.

#### Simple Analytics Dashboard

Koko Analytics is [simple analytics](https://www.kokoanalytics.com/simple-wordpress-analytics/). There are no complicated reports to dig through. A single dashboard page shows you all the important metrics.

#### Open Source Analytics

Koko Analytics is [open source analytics](https://www.kokoanalytics.com/open-source-wordpress-analytics/). The source code is released under the GPL license and freely [available on GitHub](https://github.com/ibericode/koko-analytics). Anyone can read it, inspect it and review it.

### Features 

- A beautiful analytics dashboard built right into WordPress admin.
- View statistics for your most popular posts and pages.
- See referral statistics showing which sites send you traffic.
- Path-based tracking to see analytics for any URL, including archives and search pages.
- Reliably detect returning visitors without the use of cookies.
- Exclude visits from certain WordPress user roles or IP addresses.
- Import historical statistics from Jetpack Stats, Plausible or Burst Statistics.
- Periodically clean-up historical data older than a specified number of months or years.
- A widget, Gutenberg block or shortcode to show a list of your most visited posts or pages.
- A shortcode or Gutenberg block to show the total number of pageviews to a given page.

### Premium features 

- See what countries your site is visited from with geo-location statistics.
- See what browsers, operating systems or devices your visitors are using.
- Track custom events like outbound link clicks, contact form submissions, add to cart, and more.
- Stay up-to-date with periodic analytics reports delivered to your email inbox.
- Be notified of traffic spikes over email.

You will have access to all of these benefits and more for a small yearly fee. 

[View pricing for Koko Analytics Pro here →](https://www.kokoanalytics.com/pricing/)


== Installation ==

You can install Koko Analytics in multiple ways:

1. In your WordPress admin, go to **Plugins > Add New**, search for **Koko Analytics**, and click **Install Now**.
2. [Download from WordPress.org](https://downloads.wordpress.org/plugin/koko-analytics.zip) and upload to `/wp-content/plugins/`.
3. [Download from GitHub](https://github.com/ibericode/koko-analytics/releases) and upload to `/wp-content/plugins/`.

Once activated, statistics will be collected immediately.

View your website analytics under **WP Admin > Dashboard > Analytics**.


== Frequently Asked Questions ==

If your question is not listed here, take a look at the [Koko Analytics documentation](https://www.kokoanalytics.com/docs/) on our site.

#### Does Koko Analytics set any cookies?

The use of cookies in Koko Analytics is optional.

Read more here: [Does Koko Analytics use cookies?](https://www.kokoanalytics.com/docs/faq/does-koko-analytics-use-cookies/)

#### Will using Koko Analytics slow down my site?

No. 

Koko Analytics does what it does in the most efficient way possible. Site speed will not be affected in any way.

#### Is Koko Analytics privacy-friendly?

Absolutely.

- No personal data is processed or stored.
- No third-party services are involved.
- Nothing that could identify a single visitor is stored, only aggregated counts.

#### Is Koko Analytics open-source?

Yes. 

Koko Analytics is released under the GPL license.

#### Do I need an account?

No. 

Koko Analytics runs entirely on your own site, no third party services are involved. You install the plugin and stats will start recording right away.

#### Does Koko Analytics work with pages served from caches?

Yes. 

Koko Analytics is fully compatible with all sorts of caches.


== Screenshots ==

1: A good looking analytics dashboard right inside your WordPress admin
2: View analytics over the past 2 weeks directly after logging in
3: Configure all tracking related settings.
4: Customize what your analytics dashboard looks like.
5: Register custom events for tailored analytics. [Pro]
6: Configure periodic email reports or traffic spike notification. [Pro]
7: You own your data. Export or import at will.
8: Show your most viewed posts in a widget.
9. See exactly where your website is visited from or what browsers, operating system or devices your visitors are using. [Pro]

== Changelog ==

### 2.3.3 - Apr 8, 2026

- database: fix table and column value for upserting new referrer URL's.


### 2.3.2 - Apr 7, 2026

- dashboard: draggable icon now only shows up when hovering table header, not table body
- database: prevent running database migrations concurrently 
- database: try to increase time limit to 300s before running database migrations
- database: re-acquire and extend acquired lock after every individual database migration step


### 2.3.0 - Apr 7, 2026

- tracking: improved detection of preflight requests and requests from headless browsers. 
- tracking: add more aggregation rules for google subdomains
- database: improved migration runner for more reliable database migrations
- database: use atomic upsert for upserting normalized string values (like paths and referrer urls).
- database: improved performance for pruning action.
- shortcode: fix koko_analytics_counter sometimes not working properly when used outside of post content.
- shortcode: format output of koko_analytics_counter shortcode according to localized number formatting rules.
- settings: restrict tab query parameter to whitelisted values only
- ux: allow a custom order of your dashboard components through drag and drop.
- ux: add direct link to page in the top pages component.
- ux: styling improvements to the dashboard.
- dev: add filter koko_analytics_print_html_comments to disable HTML comments with version info.


### 2.2.5 - Mar 18, 2026

- Change URL for tracking request to home_url to bypass rate limits on admin-ajax.php on some hosts. This only applies if not using the optimized endpoint.
- Format date in chart tooltip differently depending on grouping.
- Fix issue where dashboard could only fetch statistics up to 10 years back, due to pre-generated dates table.
- Prevent load_textdomain_just_in_time() warning when other plugins call wp_get_schedules() before init hook.
- Various typing improvements for issues as reported by PHPStan.


### 2.2.4 - Feb 17, 2026

- Fix fatal error on fresh plugin installation because of calling non-static method statically.
- Fix `[koko_analytics_counter]` shortcode no longer working in version 2.2.2 because of lacking function arguments.


### 2.2.2 - Feb 17, 2026

- Add hook koko_analytics_public_dashboard_headers, which fires before sending HTTP headers for public dashboard. This allows a password protected public dashboard in [Koko Analytics Pro](https://www.kokoanalytics.com/pricing/).
- Add hook koko_analytics_output_dashboard_settings which allows adding setting rows at any position on the dashboard settings page.
- Delete koko_analytics_last_aggregation_at option on plugin uninstall.
- Add gradient showing relative weight per row in the referrers table.
- Fix Jetpack & Plausible import pages not accessible since version 2.2.0.
- Minor performance improvements by changing code structure to re-use commonly used action hooks.


### 2.2.1 - Feb 02, 2026

- gutenberg: add counter block type
- tracking: add filter koko_analytics_allowed_query_vars ([example](https://github.com/ibericode/koko-analytics/blob/9a038eacf51f5eded9abc4920bbcd9c792bafc02/code-snippets/allow-query-vars.php))
- performance: rollup database migrations older than 5 years.


### 2.2.0 - Jan 21, 2026

- settings: allow plugins to register their own settings tab through the `koko_analytics_settings_tabs` filter.
- endpoint: remove duplicate require statements in case several plugins add the same file.


### 2.1.3 - Jan 12, 2026

- data export: escape path and referrer url values in data export file. Fixes a potential SQL injection vulnerability when importing a previously exported dataset containing malicious path values (CVE-2026-22850). Thanks to Hector Ruiz from [naxus-audit](https://github.com/naxus-audit) for responsibly disclosing.
- data import: only allow SQL statements affecting the Koko Analytics database
tables
- tracking: reject invalid path values per the RFC 2396 specification


### 2.1.2 - Jan 7, 2026

- tracking: accept path and post ID argument in koko_analytics.trackPageview(path, post_id) function to allow manual calls in single-page applications.
- dashboard: add group by 'year' option to chart


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

[See changelog for all versions](https://www.kokoanalytics.com/changelog/)