<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 *
 * Handles migrating post_stats data from the old (post_id based) format
 * to the new (path_id based) format.
 *
 * This class contains no HTTP concerns (nonces, redirects, etc.)
 * and can be safely called from WP-CLI, cron, migration scripts, or admin actions.
 */

namespace KokoAnalytics;

use KokoAnalytics\Normalizers\Path;

class Post_Stats_Migrator
{
    public function migrate_to_v2(): void
    {
        @set_time_limit(0);

        /** @var \wpdb $wpdb */
        global $wpdb;

        do {
            // Select all rows with a post ID but no path ID
            // Note: there is no need for an OFFSET here because we are updating rows as we go
            $results = $wpdb->get_results("SELECT DISTINCT(post_id) FROM {$wpdb->prefix}koko_analytics_post_stats WHERE post_id IS NOT NULL AND path_id IS NULL LIMIT 1000");

            // Stop once there are no more rows in result set
            if (!$results) {
                break;
            }

            // create a mapping of post_id => path
            $post_id_to_path_map = [];
            foreach ($results as $r) {
                $post_id_to_path_map["{$r->post_id}"] = $this->get_path_by_post_id($r->post_id);
            }

            // bulk insert all paths
            $upserter = new Upserter('paths', 'path');
            $path_to_path_id_map = $upserter->upsert(array_values($post_id_to_path_map));

            // update post_stats table to point to paths we just inserted
            foreach ($post_id_to_path_map as $post_id => $path) {
                $path_id = $path_to_path_id_map[$path];
                $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}koko_analytics_post_stats SET path_id = %d WHERE post_id = %d", [$path_id, $post_id]));
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

        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}koko_analytics_post_stats_old");
    }

    /**
     * Between version 2.0 and 2.0.10, there was an issue with the migration script above which would result in incorrect path ID's being returned when bulk inserting new paths.
     * This fixes every entry in the post_stats table by checking each path whether it is correct
     */
    public function fix_paths(): void
    {
        @set_time_limit(0);

        /** @var \wpdb $wpdb */
        global $wpdb;

        $offset = 0;
        $limit = 1000;
        $upserter = new Upserter('paths', 'path');

        do {
            $results = $wpdb->get_results($wpdb->prepare("SELECT post_id, path_id, p.path FROM {$wpdb->prefix}koko_analytics_post_stats s LEFT JOIN {$wpdb->prefix}koko_analytics_paths p ON p.id = s.path_id WHERE post_id IS NOT NULL AND post_id != 0 GROUP BY post_id LIMIT %d OFFSET %d", [$limit, $offset]));
            $offset += $limit;
            if (!$results) {
                break;
            }

            foreach ($results as $r) {
                $correct_path = $this->get_path_by_post_id($r->post_id);
                if ($r->path != $correct_path) {
                    // get correct path id
                    $path_to_id_map = $upserter->upsert([$correct_path]);
                    $correct_path_id = $path_to_id_map[$correct_path];

                    // update all post_stats to point to correct path_id
                    $wpdb->query($wpdb->prepare("UPDATE IGNORE {$wpdb->prefix}koko_analytics_post_stats SET path_id = %d WHERE post_id = %d", [$correct_path_id, $r->post_id]));
                }
            }
        } while (true);
    }

    private function get_path_by_post_id(int $post_id): string
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

        return Path::normalize($path);
    }
}
