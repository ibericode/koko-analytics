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
    public function __construct()
    {
        add_action('koko_analytics_aggregate_stats', array($this, 'aggregate'), 10, 0);
        add_filter('cron_schedules', array($this, 'add_interval'), 10, 1);
        add_action('init', array($this, 'maybe_setup_scheduled_event'), 10, 0);
    }

    /**
     * @param array $intervals
     */
    public function add_interval($intervals): array
    {
        $intervals['koko_analytics_stats_aggregate_interval'] = array(
            'interval' => 60, // 60 seconds
            'display'  => esc_html__('Every minute', 'koko-analytics'),
        );
        return $intervals;
    }

    public function setup_scheduled_event(): void
    {
        if (! wp_next_scheduled('koko_analytics_aggregate_stats')) {
            wp_schedule_event(time() + 60, 'koko_analytics_stats_aggregate_interval', 'koko_analytics_aggregate_stats');
        }
    }

    public function maybe_setup_scheduled_event(): void
    {
        if (! isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST' || ! is_admin()) {
            return;
        }

        $this->setup_scheduled_event();
    }

    /**
     * Reads the buffer file into memory and moves data into the MySQL database (in bulk)
     *
     * @throws Exception
     */
    public function aggregate(): void
    {
        update_option('koko_analytics_last_aggregation_at', time(), true);

        // init pageview aggregator
        $pageview_aggregator = new Pageview_Aggregator();

        // read pageviews buffer file into array
        $filename = get_buffer_filename();
        if (! \is_file($filename)) {
            // no pageviews were collected since last run, so we have nothing to do
            return;
        }

        // rename file to temporary location so nothing new is written to it while we process it
        $tmp_filename = \dirname($filename) . '/pageviews-' . time() . '.php';
        $renamed = \rename($filename, $tmp_filename);
        if ($renamed !== true) {
            if (WP_DEBUG) {
                throw new Exception('Error renaming buffer file.');
            }
            return;
        }

        // open file for reading
        $file_handle = \fopen($tmp_filename, 'r');
        if (! $file_handle) {
            if (WP_DEBUG) {
                throw new Exception('Error opening buffer file for reading.');
            }
            return;
        }

        // read and ignore first line (the PHP header that prevents direct file access)
        \fgets($file_handle, 1024);

        while (($line = \fgets($file_handle, 1024)) !== false) {
            $line = \trim($line);
            if ($line === '' || $line === '<?php exit; ?>') {
                continue;
            }

            $params = \explode(',', $line);
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
}
