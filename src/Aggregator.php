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
    private const CHUNK_SIZE = 10000;

    /**
     * Reads the buffer file in chunks and moves data into the MySQL database.
     *
     * @throws Exception
     */
    public function run(): void
    {
        $buffer_file = get_buffer_filename();

        // if buffer file does not exist, nothing happened since last aggregation
        if (! \is_file($buffer_file)) {
            return;
        }

        // init pageview aggregator
        $pageview_aggregator = new Pageview_Aggregator();
        $records_in_chunk    = 0;

        // rename file to temporary location so nothing new is written to it while we process it
        $tmp_filename = $buffer_file . '.busy';
        $renamed      = \rename($buffer_file, $tmp_filename);
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
            $type = \array_shift($params);

            // core aggregator
            $pageview_aggregator->line($type, $params);

            // add-on aggregators
            do_action('koko_analytics_aggregate_line', $type, $params);

            ++$records_in_chunk;
            if ($records_in_chunk === self::CHUNK_SIZE) {
                $this->flush($pageview_aggregator);
                $records_in_chunk = 0;
            }
        }

        if ($records_in_chunk > 0) {
            $this->flush($pageview_aggregator);
        }

        // Finalize state that remains bounded across chunks, such as realtime counts.
        $pageview_aggregator->finish();

        // close file & remove it from filesystem
        \fclose($file_handle);
        \unlink($tmp_filename);

        // signal that the entire buffer file was processed
        do_action('koko_analytics_aggregate_finish');

        update_option('koko_analytics_last_aggregation_at', \time(), false);
    }

    private function flush(Pageview_Aggregator $pageview_aggregator): void
    {
        $pageview_aggregator->finish(false);

        // Allow add-on aggregators to persist and reset their in-memory state.
        do_action('koko_analytics_aggregate_chunk_finish');
    }
}
