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
        add_filter('cron_schedules', [$this, 'add_interval'], 10, 1);
        add_action('koko_analytics_aggregate_stats', [$this, 'aggregate'], 10, 0);
        add_action('koko_analytics_save_settings', [$this, 'setup_scheduled_event'], 10, 0);

        register_activation_hook(KOKO_ANALYTICS_PLUGIN_FILE, [$this, 'setup_scheduled_event']);
        register_deactivation_hook(KOKO_ANALYTICS_PLUGIN_FILE, [$this, 'clear_scheduled_event']);
    }

    /**
     * @param array $intervals
     */
    public function add_interval($intervals): array
    {
        $intervals['koko_analytics_stats_aggregate_interval'] = [
            'interval' => 60, // 60 seconds
            'display'  => esc_html__('Every minute', 'koko-analytics'),
        ];
        return $intervals;
    }

    public function setup_scheduled_event(): void
    {
        if (! wp_next_scheduled('koko_analytics_aggregate_stats')) {
            wp_schedule_event(time() + 60, 'koko_analytics_stats_aggregate_interval', 'koko_analytics_aggregate_stats');
        }
    }

    public function clear_scheduled_event(): void
    {
        wp_clear_scheduled_hook('koko_analytics_aggregate_stats');
    }

    private function get_buffer_files(): array
    {
        $upload_dir = get_upload_dir();
        return glob("{$upload_dir}/buffer-*.php");
    }

    /**
     * Reads the buffer file into memory and moves data into the MySQL database (in bulk)
     *
     * @throws Exception
     */
    public function aggregate(): void
    {
        update_option('koko_analytics_last_aggregation_at', \time(), true);

        $buffer_files = $this->get_buffer_files();
        if (empty($buffer_files)) {
            return;
        }

        // init pageview aggregator
        $pageview_aggregator = new Pageview_Aggregator();

        foreach ($buffer_files as $buffer_file) {
            // parse date from filename
            $date = preg_match("/buffer-(.*)\.php/", basename($buffer_file), $matches);
            $date = $matches[1];

            // rename file to temporary location so nothing new is written to it while we process it
            $tmp_filename = $buffer_file . '.busy';
            $renamed = \rename($buffer_file, $tmp_filename);
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
            $pageview_aggregator->finish($date);
            do_action('koko_analytics_aggregate_finish', $date);
        }

        // ensure scheduled event is ready to go again
        $this->setup_scheduled_event();
    }
}
