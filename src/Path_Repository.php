<?php

namespace KokoAnalytics;

class Path_Repository
{
    public static function upsert(array $paths): array
    {
        /** @var wpdb $wpdb */
        global $wpdb;

        $map = array_fill_keys($paths, 0);

        $placeholders  = rtrim(str_repeat('%s,', count($paths)), ',');
        $results       = $wpdb->get_results($wpdb->prepare("SELECT id, path FROM {$wpdb->prefix}koko_analytics_paths p WHERE p.path IN({$placeholders})", $paths));

        // fill map with path ID's from database
        foreach ($results as $r) {
            $map[$r->path] = $r->id;
        }

        // get all entries without an ID
        $new_values = array_keys($map, 0);

        if (count($new_values) > 0) {
            $placeholders = rtrim(str_repeat('(%s),', count($new_values)), ',');
            $wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}koko_analytics_paths(path) VALUES {$placeholders}", $new_values));
            $last_insert_id = $wpdb->insert_id;

            foreach ($new_values as $key) {
                $map[$key] = $last_insert_id++;
            }
        }

        return $map;
    }
}
