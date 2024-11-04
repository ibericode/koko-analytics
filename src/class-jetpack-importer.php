<?php

namespace KokoAnalytics;

class Jetpack_Importer
{
    public function __construct()
    {
        add_action('koko_analytics_show_jetpack_importer_page', [$this, 'show_page']);
        add_action('koko_analytics_start_jetpack_import', [$this, 'start_import']);
        add_action('koko_analytics_jetpack_import_chunk', [$this, 'import_chunk']);
    }

    public function show_page(): void
    {
        if (!current_user_can('manage_koko_analytics')) {
            return;
        }

        ?>
        <div class="wrap" style="max-width: 820px;">

            <?php if (isset($_GET['success']) && $_GET['success'] == 0) { ?>
                <div class="notice notice-error is-dismissible"><p>Sorry, something went wrong in trying to retrieve your stats from WordPress.com. Please check whether your API key and blog URL are correct.</p></div>
            <?php } ?>

            <?php if (isset($_GET['success']) && $_GET['success'] == 1) { ?>
                <div class="notice notice-success is-dismissible"><p>Big success! Your stats are now imported into Koko Analytics.</p></div>
            <?php } ?>

            <h1>Import analytics from JetPack Stats</h1>
            <p>To import your historical analytics data from JetPack Stats into Koko Analytics, provide your WordPress.com API key and blog URL in the field below.</p>

            <form method="post" onsubmit="return confirm('Are you sure you want to import statistics between ' + this['date-start'].value + ' and ' +this['date-end'].value + '? This will overwrite any existing data in your Koko Analytics database tables.');" action="<?php echo esc_attr(get_admin_url(null, '/index.php?page=koko-analytics&tab=jetpack_importer')); ?>">

                <input type="hidden" name="koko_analytics_action" value="start_jetpack_import">
                <?php wp_nonce_field('koko_analytics_start_jetpack_import'); ?>

                <table class="form-table">
                    <tr>
                        <th><label for="wpcom-api-key">WordPress.com API key</label></th>
                        <td>
                            <input id="wpcom-api-key" type="text" class="regular-text" name="wpcom-api-key" required>
                            <p class="description">You can <a href="https://apikey.wordpress.com/">find your WordPress.com API key here</a>.</p>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="wpcom-blog-uri">Blog URL</label></th>
                        <td>
                            <input id="wpcom-blog-uri" type="text" class="regular-text" name="wpcom-blog-uri" value="<?php echo esc_attr(get_site_url()); ?>" required>
                            <p class="description">The full URL to the root directory of your blog. Including the full path.</p>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="date-start">Start date</label></th>
                        <td>
                            <input id="date-start" name="date-start" type="date" value="<?php echo esc_attr(date('Y-m-d', strtotime('-1 year'))); ?>" required>
                             <p class="description">The earliest date for which to import data. You should probably set this to the date that you installed and activated Jetpack Stats.</p>

                        </td>
                    </tr>

                    <tr>
                        <th><label for="date-end">End date</label></th>
                        <td>
                            <input id="date-end" name="date-end" type="date" value="<?php echo esc_attr(date('Y-m-d')); ?>" required>
                            <p class="description">The last date for which to import data. You should probably set this to just before the date that you installed and activated Koko Analytics.</p>

                        </td>
                    </tr>
                </table>

                <p>
                    <button type="submit" class="button">Import analytics data</button>
                </p>
            </form>

            <div class="ka-margin-m">
                <h3>Things to know before running the import</h3>
                <p>Importing data for a given date range will add to any existing data. The import process can not be reverted unless you reinstate a back-up of your database in its current state.</p>
                <p>It's also important to know that JetPack doesn't provide data for the distinct number of visitors, so the data imported will only import the total number of pageviews for each post and therefore differ slightly from data collected by Koko Analytics itself.</p>
            </div>
        </div>
        <?php
    }

    public function start_import(): void
    {
        // authorize user
        if (!current_user_can('manage_koko_analytics')) {
            return;
        }

        // verify nonce
        check_admin_referer('koko_analytics_start_jetpack_import');


        // save params
        $params = [
            'wpcom-api-key' => trim($_POST['wpcom-api-key'] ?? ''),
            'wpcom-blog-uri' => trim($_POST['wpcom-blog-uri'] ?? ''),
            'date-start' => trim($_POST['date-start'] ?? ''),
            'date-end' => trim($_POST['date-end'] ?? ''),
        ];

        // all params are required
        if ($params['wpcom-api-key'] === '' || $params['wpcom-blog-uri'] === '' || $params['date-start'] === '' || $params['date-end'] === '') {
            wp_safe_redirect(get_admin_url(null, '/index.php?page=koko-analytics&tab=jetpack_importer&success=0'));
            exit;
        }

        // first chunk is 30 days after date-start
        try {
            $date_start = new \DateTimeImmutable($params['date-start']);
            $date_end = new \DateTimeImmutable($params['date-end']);
            if ($date_end < $date_start) {
                throw new \Exception("End date must be after start date");
            }
        } catch (\Exception $e) {
            wp_safe_redirect(get_admin_url(null, '/index.php?page=koko-analytics&tab=jetpack_importer&success=0'));
            exit;
        }

        // params are valid; let's go!
        update_option('koko_analytics_jetpack_import_params', $params, false);

        // work backwards from end date, so most recent stats first
        $chunk_end = $date_end;
        $chunk_size = 30;
        $chunk_start = $date_end->modify("-{$chunk_size} days");
        if ($chunk_start < $date_start) {
            $chunk_start = $date_start;
            $chunk_size = $date_end->diff($date_start)->days;
        }

        // redirect to first chunk
        wp_safe_redirect(add_query_arg(['koko_analytics_action' => 'jetpack_import_chunk', 'chunk_size' => $chunk_size, 'chunk_end' => $chunk_end->format('Y-m-d'), '_wpnonce' => wp_create_nonce('koko_analytics_jetpack_import_chunk')]));
        exit;
    }

    public function import_chunk(): void
    {
        // authorize
        if (!current_user_can('manage_koko_analytics')) {
            return;
        }

        // verify nonce
        check_admin_referer('koko_analytics_jetpack_import_chunk');

        // get params
        $params = get_option('koko_analytics_jetpack_import_params');
        if (!$params) {
            wp_safe_redirect(get_admin_url(null, '/index.php?page=koko-analytics&tab=jetpack_importer&success=0'));
            exit;
        }

        $chunk_end = trim($_GET['chunk_end']);
        $chunk_size = (int) trim($_GET['chunk_size']);
        $date_end = new \DateTimeImmutable($params['date-end']);
        $date_start = new \DateTimeImmutable($params['date-start']);
        $chunk_end = new \DateTimeImmutable($chunk_end);

        // calculate next chunk end date and actual size of current chunk
        $next_chunk_end = $chunk_end->modify("-{$chunk_size} days");
        if ($next_chunk_end < $date_start) {
            $chunk_size = $chunk_end->diff($date_start)->days;
        }

        // import this chunk
        if (! $this->perform_chunk_import($params['wpcom-api-key'], $params['wpcom-blog-uri'], $chunk_end, $chunk_size)) {
            delete_option('koko_analytics_jetpack_import_params');
            wp_safe_redirect(get_admin_url(null, '/index.php?page=koko-analytics&tab=jetpack_importer&success=0'));
            exit;
        }

        // If we're done, redirect to success page
        if ($next_chunk_end < $date_start) {
            delete_option('koko_analytics_jetpack_import_params');
            wp_safe_redirect(get_admin_url(null, '/index.php?page=koko-analytics&tab=jetpack_importer&success=1'));
            exit;
        }

        $url = add_query_arg(['koko_analytics_action' => 'jetpack_import_chunk', 'chunk_size' => $chunk_size, 'chunk_end' => $next_chunk_end->format('Y-m-d'), '_wpnonce' => wp_create_nonce('koko_analytics_jetpack_import_chunk')]);

        $chunk_start = $chunk_end->modify("-{$chunk_size} days");
        $chunks_left = ceil($chunk_end->diff($date_start)->days / $chunk_size);

        // we could do a wp_safe_redirect() here
        // but instead we send some HTML to the client and perform a client-side redirect just so the user knows we're still alive and working
        ?>
        <style>body { background: #f0f0f1; color: #3c434a; font-family: sans-serif; font-size: 16px; line-height: 1.5; padding: 32px; }</style>
        <meta http-equiv="refresh" content="1; url=<?php echo esc_attr($url); ?>">
        <h1>Liberating your data... Please wait.</h1>
        <p>Importing stats between <strong><?php echo $chunk_start->format('Y-m-d'); ?></strong> and <strong><?php echo $chunk_end->format('Y-m-d'); ?></strong>.</p>
        <p>Please do not close this browser tab while the importer is running.</p>
        <p>Estimated time left: <?php echo round($chunks_left * 1.5); ?> seconds.</p>
        <?php
        exit;
    }

    public function perform_chunk_import(string $api_key, string $blog_uri, \DateTimeImmutable $date_end, int $chunk_size): bool
    {
        $blog_uri = urlencode($blog_uri);
        $url = "https://stats.wordpress.com/csv.php?api_key={$api_key}&blog_uri={$blog_uri}&end={$date_end->format('Y-m-d')}&table=postviews&format=json&days={$chunk_size}&limit=-1";
        $response = wp_remote_get($url);
        if (!$response || wp_remote_retrieve_response_code($response) >= 400) {
            $status = wp_remote_retrieve_response_code($response);
            $message = wp_remote_retrieve_response_message($response);
            $body = wp_remote_retrieve_body($response);
            error_log("Koko Analytics - JetPack Importer: received error response from WordPress.com API: {$status} {$message}\n\n{$body}\n");
            return false;
        }

        $body = wp_remote_retrieve_body($response);

        try {
            $data = json_decode($body, null, 512, JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            error_log("Koko Analytics - JetPack Importer: received non-JSON response from WordPress.com API: " . wp_remote_retrieve_body($response));
            return false;
        }

        // API returns `null` for no data between two given dates
        // Let's turn it into an array instead
        if ($data === null) {
            $data = [];
        }

        // We now have an array of days in the following format:
        // [ [ "date" => "2020-10-31", "postviews" => [ [ "post_id" => 1, "views" => 2 ] ] ] ]
        global $wpdb;

        foreach ($data as $item) {
            $site_views = 0;

            // update post stats for this date one-by-one
            // TODO: We could make this more efficient by executing a single bulk query
            foreach ($item->postviews as $postviews) {
                $site_views += $postviews->views;

                $query = $wpdb->prepare("INSERT INTO {$wpdb->prefix}koko_analytics_post_stats (date, id, visitors, pageviews) VALUES (%s, %d, %d, %d) ON DUPLICATE KEY UPDATE visitors = visitors + VALUES(visitors), pageviews = pageviews + VALUES(pageviews);", [$item->date, $postviews->post_id, $postviews->views, $postviews->views]);
                $wpdb->query($query);

                if ($wpdb->last_error !== '') {
                    error_log("Koko Analytics - JetPack Importer: database error trying to update site_stats: " . $wpdb->last_error);
                }
            }

            // update site stats
            $query = $wpdb->prepare("INSERT INTO {$wpdb->prefix}koko_analytics_site_stats (date, visitors, pageviews) VALUES (%s, %d, %d) ON DUPLICATE KEY UPDATE visitors = visitors + VALUES(visitors), pageviews = pageviews + VALUES(pageviews);", [$item->date, $site_views, $site_views]);
            $wpdb->query($query);
            if ($wpdb->last_error !== '') {
                error_log("Koko Analytics - JetPack Importer: database error trying to update site_stats: " . $wpdb->last_error);
            }
        }

        return true;
    }
}
