<?php

namespace KokoAnalytics;

class Referrer_Repository
{
    public static function upsert(array $values): array
    {
        /** @var wpdb $wpdb */
        global $wpdb;

        $map = array_fill_keys($values, 0);

        $placeholders  = rtrim(str_repeat('%s,', count($values)), ',');
        $results       = $wpdb->get_results($wpdb->prepare("SELECT id, url FROM {$wpdb->prefix}koko_analytics_referrer_urls r WHERE r.url IN({$placeholders})", $values));

        // fill map with normalized ID's from database
        foreach ($results as $r) {
            $map[$r->url] = $r->id;
        }

        // get all entries without an ID
        $new_values = array_keys($map, 0);

        if (count($new_values) > 0) {
            $placeholders = rtrim(str_repeat('(%s),', count($new_values)), ',');
            $wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}koko_analytics_referrer_urls(url) VALUES {$placeholders}", $new_values));
            $last_insert_id = $wpdb->insert_id;

            foreach ($new_values as $key) {
                $map[$key] = $last_insert_id++;
            }
        }

        return $map;
    }
}
