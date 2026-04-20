=== Koko Analytics - Privacy Friendly Statistics for WordPress ===
Contributors: Ibericode, DvanKooten, kokoanalytics
Tags: analytics, google analytics, statistics, stats, privacy
Requires at least: 6.0
Tested up to: 6.9.4
Stable tag: 2.3.4
License: GPL-3.0-or-later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Requires PHP: 7.4

Easy, fast, privacy-friendly statistics. A simpler Google Analytics alternative built right into WordPress.

== Description ==

Koko Analytics provides website analytics and visitor statistics directly inside your WordPress dashboard without relying on external services. It is privacy-friendly, lightweight, open source, and easy to use.

Fully GDPR, CCPA and PECR compliant by design: no personal data is processed or stored, everything runs on your own server and can be used without cookies.

You can [view a live demo of Koko Analytics here](https://www.kokoanalytics.com/koko-analytics-dashboard/).

#### Privacy Friendly Analytics

Koko Analytics is [privacy friendly analytics](https://www.kokoanalytics.com/privacy-focused-wordpress-analytics/). No personal data is processed or stored, all measurements are carried out completely anonymously and nothing is ever shared with any third-party service. 

#### Lightweight Statistics

Koko Analytics is [lightweight analytics](https://www.kokoanalytics.com/lightweight-wordpress-analytics/). It adds less than 1 kilobyte of data to your HTML and is fully compatible with pages served from any kind of cache. WordPress is bypassed entirely for its collection endpoint, making the impact on your site's performance as close to zero as possible. Fact: there is no faster statistics plugin for WordPress.

#### Simple Analytics Dashboard

Koko Analytics is [simple analytics](https://www.kokoanalytics.com/simple-wordpress-analytics/). There are no complicated reports to dig through. A single dashboard page shows you all the important metrics.

#### Open Source Analytics

Koko Analytics is [open source analytics](https://www.kokoanalytics.com/open-source-wordpress-analytics/) released under the GPL license, just like WordPress itself. It is built in the open: anyone can verify how it works and no company can lock you in or take it away.

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

All of this is available with a Koko Analytics Pro license. [View pricing for Koko Analytics Pro here →](https://www.kokoanalytics.com/pricing/)


== Installation ==

You can install Koko Analytics in multiple ways:

1. In your WordPress admin, go to **Plugins > Add New**, search for **Koko Analytics**, and click **Install Now**.
2. [Download from WordPress.org](https://downloads.wordpress.org/plugin/koko-analytics.zip) and upload to `/wp-content/plugins/`.
3. [Download from GitHub](https://github.com/ibericode/koko-analytics/releases) and upload to `/wp-content/plugins/`.

Once activated, Koko Analytics starts collecting statistics right away — no configuration needed.

View your website analytics under **WP Admin > Dashboard > Analytics**.


== Frequently Asked Questions ==

If your question is not listed here, take a look at the [Koko Analytics documentation](https://www.kokoanalytics.com/docs/) on our site.

#### Does Koko Analytics set any cookies?

The use of cookies in Koko Analytics is optional.

Read more here: [Does Koko Analytics use cookies?](https://www.kokoanalytics.com/docs/faq/does-koko-analytics-use-cookies/)

#### Will using Koko Analytics slow down my site?

No. 

Koko Analytics is [lightweight analytics](https://www.kokoanalytics.com/lightweight-wordpress-analytics/). It adds less than 1 kilobyte of data to your HTML and is fully compatible with pages served from any kind of cache. WordPress is bypassed entirely for its collection endpoint, making the impact on your site's performance as close to zero as possible. Fact: there is no faster statistics plugin for WordPress.

#### Is Koko Analytics privacy-friendly?

Absolutely.

- No personal data is processed or stored.
- No third-party services are involved.
- Nothing that could identify a single visitor is stored, only aggregated counts.

#### Is Koko Analytics open-source?

Yes. 

Koko Analytics is [open source analytics](https://www.kokoanalytics.com/open-source-wordpress-analytics/) released under the GPL license, just like WordPress itself. It is built in the open: anyone can verify how it works and no company can lock you in or take it away.

#### Do I need an account?

No. 

Koko Analytics runs entirely on your own site, no third party services are involved. You install the plugin and stats will start recording right away.

#### Does Koko Analytics work with pages served from caches?

Yes. 

Koko Analytics is fully compatible with all sorts of caches.


== Screenshots ==

1. A good looking analytics dashboard right inside your WordPress admin
2. View analytics over the past 2 weeks directly after logging in
3. Configure all tracking related settings.
4. Customize what your analytics dashboard looks like.
5. Register custom events for tailored analytics. [Pro]
6. Configure periodic email reports or traffic spike notification. [Pro]
7. You own your data. Export or import at will.
8. Show your most viewed posts in a widget.
9. See exactly where your website is visited from or what browsers, operating system or devices your visitors are using. [Pro]

== Changelog ==


### 2.3.4 - Apr 20, 2026

- fix: access to Jetpack and Plausible importer pages.
- fix: database warning because of unexisting table on fresh installs.
- ux: table rows selectable again.
- seo: remove canonical URL from public dashboard (because it is already noindex).
- database: change default database purge treshold to 3 years (down from 5).
- dashboard: don't listen to query string argument for public dashboard if pretty permalinks are enabled.


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

View full [Koko Analytics changelog](https://www.kokoanalytics.com/changelog/).