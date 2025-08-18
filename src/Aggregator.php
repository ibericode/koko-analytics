<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

use Exception;

class Aggregator
{
    /**
     * Reads the buffer file into memory and moves data into the MySQL database (in bulk)
     *
     * @throws Exception
     */
    public static function run(): void
    {
        update_option('koko_analytics_last_aggregation_at', \time(), true);

        $buffer_file = get_buffer_filename();

        // if buffer file does not exist, nothing happened since last aggregation
        if (! \is_file($buffer_file)) {
            return;
        }

        // init pageview aggregator
        $pageview_aggregator = new Pageview_Aggregator();

        // rename file to temporary location so nothing new is written to it while we process it
        $tmp_filename = $buffer_file . '.busy';
        $renamed = \rename($buffer_file, $tmp_filename);
        if ($renamed !== true) {
            if (WP_DEBUG) {
                throw new Exception('Error renaming buffer file.');
            } else {
                error_log('Koko Analytics: error renaming buffer file');
            }
            return;
        }

        // open file for reading
        $file_handle = \fopen($tmp_filename, 'r');
        if (! $file_handle) {
            if (WP_DEBUG) {
                throw new Exception('Error opening buffer file for reading.');
            } else {
                error_log('Koko Analytics: error opening buffer file for reading');
            }
            return;
        }

        while (($line = \fgets($file_handle)) !== false) {
            $line = \trim($line);
            if ($line === '' || $line === '<?php exit; ?>') {
                continue;
            }

            $params = \unserialize($line, ['allowed_classes' => false]);
            if (! \is_array($params)) {
                error_log('Koko Analytics: unserialize error encountered while processing line in buffer file');
                continue;
            }
            $type   = \array_shift($params);

            // core aggregator
            $pageview_aggregator->line($type, $params);

            // add-on aggregators
            do_action('koko_analytics_aggregate_line', $type, $params);
        }

        // close file & remove it from filesystem
        \fclose($file_handle);
        \unlink($tmp_filename);

        // tell aggregators to write their results to the database
        $pageview_aggregator->finish();
        do_action('koko_analytics_aggregate_finish');
    }

    public static function setup_scheduled_event(): void
    {
        if (! wp_next_scheduled('koko_analytics_aggregate_stats')) {
            wp_schedule_event(time() + 60, 'koko_analytics_stats_aggregate_interval', 'koko_analytics_aggregate_stats');
        }
    }

    public static function clear_scheduled_event(): void
    {
        wp_clear_scheduled_hook('koko_analytics_aggregate_stats');
    }
}
