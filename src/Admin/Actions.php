<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics\Admin;

use KokoAnalytics\Endpoint_Installer;
use KokoAnalytics\Data_Exporter;
use KokoAnalytics\Data_Importer;
use KokoAnalytics\Fingerprinter;
use KokoAnalytics\Normalizers\Normalizer;
use KokoAnalytics\Path_Repository;

use function KokoAnalytics\get_settings;

class Actions
{
    public static function install_optimized_endpoint(): void
    {
        $result = Endpoint_Installer::install();
        $type = $result === true ? 'success' : 'warning';
        $message = $result === true ? __('Successfully installed optimized endpoint.', 'koko-analytics') : $result;
        wp_safe_redirect(add_query_arg([ 'notice' => ['type' => $type, 'message' => $message] ], wp_get_referer()));
        exit;
    }

    public static function save_settings(): void
    {
        if (!current_user_can('manage_koko_analytics')) {
            return;
        }

        check_admin_referer('koko_analytics_save_settings');

        $posted                        = $_POST['koko_analytics_settings'];
        $settings                            = get_settings();

        // get rid of deprecated setting keys
        unset($settings['use_cookie']);

        $settings['exclude_ip_addresses']    = array_filter(array_map('trim', explode(PHP_EOL, str_replace(',', PHP_EOL, strip_tags($posted['exclude_ip_addresses'])))), function ($value) {
            return $value !== '';
        });
        $settings['exclude_user_roles']      = $posted['exclude_user_roles'] ?? [];
        $settings['prune_data_after_months'] = abs((int) $posted['prune_data_after_months']);
        $settings['is_dashboard_public']     = (int) $posted['is_dashboard_public'];
        $settings['default_view']            = trim($posted['default_view']);
        $settings['tracking_method'] = in_array($posted['tracking_method'], ['cookie', 'fingerprint', 'none']) ? $posted['tracking_method'] : 'cookie';

        $settings = apply_filters('koko_analytics_sanitize_settings', $settings, $posted);
        update_option('koko_analytics_settings', $settings, true);

        // maybe create sessions directory & initial seed file
        if ($settings['tracking_method'] === 'fingerprint') {
            Fingerprinter::create_storage_dir();
            Fingerprinter::setup_scheduled_event();
        }

        // Re-create optimized endpoint to ensure its contents are up-to-date
        Endpoint_Installer::install();

        wp_safe_redirect(add_query_arg(['settings-updated' => true], wp_get_referer()));
        exit;
    }

    public static function migrate_post_stats_to_v2(): void
    {
        @set_time_limit(0);

        /** @var \wpdb $wpdb */
        global $wpdb;

        do {
            // Select all rows with a post ID but no path ID
            // Note: there is no need for an OFFSET here because we are updating rows as we go
            $results = $wpdb->get_results("SELECT DISTINCT(post_id) FROM {$wpdb->prefix}koko_analytics_post_stats WHERE post_id IS NOT NULL AND path_id IS NULL LIMIT 500");


            // Stop once there are no more rows in result set
            if (!$results) {
                break;
            }

            // create a mapping of post_id => path
            $post_id_to_path_map = [];
            foreach ($results as $r) {
                $post_id_to_path_map["{$r->post_id}"] = self::get_path_by_post_id($r->post_id);
            }

            // bulk insert all paths
            $path_to_path_id_map = Path_Repository::upsert(array_values($post_id_to_path_map));

            // update post_stats table to point to paths we just inserted
            foreach ($post_id_to_path_map as $post_id => $path) {
                $path_id = $path_to_path_id_map[$path];
                $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}koko_analytics_post_stats SET path_id = %d WHERE post_id = %d", [ $path_id, $post_id ]));
            }
        } while (true);

        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}koko_analytics_post_stats_old");
        $wpdb->query("RENAME TABLE {$wpdb->prefix}koko_analytics_post_stats TO {$wpdb->prefix}koko_analytics_post_stats_old");

        $wpdb->query("CREATE TABLE {$wpdb->prefix}koko_analytics_post_stats (
            date DATE NOT NULL,
            path_id INT UNSIGNED NOT NULL,
            post_id INT UNSIGNED NOT NULL DEFAULT 0,
            visitors INT UNSIGNED NOT NULL DEFAULT 0,
            pageviews INT UNSIGNED NOT NULL DEFAULT 0,
            PRIMARY KEY (date, path_id)
        ) ENGINE=INNODB CHARACTER SET=ascii");

        $wpdb->query("INSERT INTO {$wpdb->prefix}koko_analytics_post_stats(date, path_id, post_id, visitors, pageviews) SELECT date, path_id, post_id, SUM(visitors), SUM(pageviews) FROM {$wpdb->prefix}koko_analytics_post_stats_old GROUP BY date, path_id");
    }

    public static function migrate_referrer_stats_to_v2(): void
    {
        @set_time_limit(0);

        /** @var \wpdb $wpdb */
        global $wpdb;

        // some of the UPDATE queries below can fail, we don't want to exit when that happens
        $wpdb->hide_errors();

        // delete unused referrer URL's
        $wpdb->query("DELETE FROM {$wpdb->prefix}koko_analytics_referrer_urls WHERE id NOT IN(SELECT DISTINCT(id) FROM {$wpdb->prefix}koko_analytics_referrer_stats)");

        do {
            $results = $wpdb->get_results("SELECT id, url FROM {$wpdb->prefix}koko_analytics_referrer_urls WHERE url LIKE 'http://%' or url LIKE 'https://%' LIMIT 500");
            if (!$results) {
                break;
            }

            foreach ($results as $row) {
                $row->url = Normalizer::referrer($row->url);

                //  if row is seriously malformed, delete it
                if ($row->url === '') {
                    $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}koko_analytics_referrer_stats WHERE id = %d", [ $row->id ]));
                    $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}koko_analytics_referrer_urls WHERE id = %d", [ $row->id ]));
                    continue;
                }

                // check if normalized url already has an entry
                $id = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}koko_analytics_referrer_urls WHERE url = %s LIMIT 1", [$row->url]));
                if ($id) {
                    // grab all rows in stats table pointing to old ID
                    $stats = $wpdb->get_results($wpdb->prepare("SELECT date, id, pageviews, visitors FROM {$wpdb->prefix}koko_analytics_referrer_stats WHERE id = %d", [$row->id]));

                    // update rows (if exist) with values from each date, id entry
                    foreach ($stats as $s) {
                        if ($wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}koko_analytics_referrer_stats SET visitors = visitors + %d, pageviews = pageviews + %d WHERE date = %s and id = %d", [$s->visitors, $s->pageviews, $s->date, $id]))) {
                            $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}koko_analytics_referrer_stats WHERE date = %s and id = %d", [$s->date, $s->id]));
                        }
                    }

                    // try to update all rows to new id (this will fail for some rows)
                    $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}koko_analytics_referrer_stats SET id = %d WHERE id = %d", [ $id, $row->id ]));

                    // delete rows that still have old ID at this point
                    $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}koko_analytics_referrer_stats WHERE id = %d", [ $row->id ]));
                    $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}koko_analytics_referrer_urls WHERE id = %d", [ $row->id ]));
                } else {
                    // otherwise change entry to normalized version
                    $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}koko_analytics_referrer_urls SET url = %s WHERE id = %d", [ $row->url, $row->id ]));
                }
            }
        } while ($results);
    }

    /**
     * Between version 2.0 and 2.0.10, there was an issue with the migration script above which would result in incorrect path ID's being returned when bulk inserting new paths.
     * This fixes every entry in the post_stats table by checking each path whether it is correct
     */
    public static function fix_post_paths_after_v2(): void
    {
        @set_time_limit(0);

        /** @var \wpdb $wpdb */
        global $wpdb;

        $offset = 0;
        $limit = 500;

        do {
            $results = $wpdb->get_results($wpdb->prepare("SELECT post_id, path_id, p.path FROM {$wpdb->prefix}koko_analytics_post_stats s LEFT JOIN {$wpdb->prefix}koko_analytics_paths p ON p.id = s.path_id WHERE post_id IS NOT NULL AND post_id != 0 GROUP BY post_id LIMIT %d OFFSET %d", [$limit, $offset]));
            $offset += $limit;
            if (!$results) {
                break;
            }

            foreach ($results as $r) {
                $correct_path = self::get_path_by_post_id($r->post_id);
                if ($r->path != $correct_path) {
                    // get correct path id
                    $path_to_id_map = Path_Repository::upsert([$correct_path]);
                    $correct_path_id = $path_to_id_map[$correct_path];

                    // update all post_stats to point to correct path_id
                    $wpdb->query($wpdb->prepare("UPDATE IGNORE {$wpdb->prefix}koko_analytics_post_stats SET path_id = %d WHERE post_id = %d", [$correct_path_id, $r->post_id]));
                }
            }
        } while (true);
    }

    private static function get_path_by_post_id($post_id)
    {
        $home_url = home_url('/');
        $post_permalink = $post_id ? get_permalink($post_id) : $home_url;
        if (!$post_permalink) {
            $post_permalink = "$home_url?p={$post_id}";
        }

        $url_parts = parse_url($post_permalink);
        $path = $url_parts['path'] ?? '/';
        if (!empty($url_parts['query'])) {
            $path .= '?' . $url_parts['query'];
        }

        return Normalizer::path($path);
    }
}
