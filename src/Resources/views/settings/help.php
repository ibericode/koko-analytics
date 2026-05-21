<?php
if (!defined('ABSPATH')) {
    exit;
}

$migrations = \KokoAnalytics\get_migrations();
$database_stats = \KokoAnalytics\get_database_stats()->get();
$last_aggregation_at = (int) get_option('koko_analytics_last_aggregation_at', 0);
$debug_info = [
    'PHP: ' . PHP_VERSION,
    'MySQL: ' . $GLOBALS['wpdb']->db_version(),
    'Koko Analytics: ' . KOKO_ANALYTICS_VERSION,
    '    Database version: ' . $migrations->get_current_version() . ' / ' . $migrations->get_latest_version() . ' (current / latest)',
    '    Database size: ' . size_format($database_stats['total_size']) . ' across ~' . number_format_i18n($database_stats['total_rows']) . ' rows',
    '    Last aggregation: ' . date(DATE_W3C, $last_aggregation_at) . ' (' . (time() - $last_aggregation_at) . ' seconds ago)',
];

if (defined('KOKO_ANALYTICS_PRO_VERSION')) {
    $debug_info[] = 'Koko Analytics Pro: ' . KOKO_ANALYTICS_PRO_VERSION;
    $debug_info[] = '    Database version: ' . get_option('koko_analytics_pro_version', '');
}
?>
<h2 class="mt-0 mb-3"><?= esc_html__('Help', 'koko-analytics') ?></h2>
 <div class="mb-5">
    <ul class="ul-square">
        <li><?php printf(esc_html__('Have a look at our %1$sknowledge base%2$s for help with configuring and using Koko Analytics.', 'koko-analytics'), '<a href="https://www.kokoanalytics.com/docs/#utm_source=koko-analytics&amp;utm_medium=link&amp;utm_campaign=free-plugin-settings-help-docs" target="_blank">', '</a>'); ?></li>
        <li><?php printf(esc_html__('Go through our %1$srepository of sample code snippets%2$s for inspiration on modifying the default Koko Analytics behavior.', 'koko-analytics'), '<a href="https://github.com/ibericode/koko-analytics/tree/main/code-snippets" target="_blank">', '</a>'); ?></li>
        <li><?php printf(esc_html__('Vote on %1$snew features you would like to have in Koko Analytics%2$s.', 'koko-analytics'), '<a href="https://github.com/ibericode/koko-analytics/discussions?discussions_q=is%3Aopen+sort%3Atop" target="_blank">', '</a>'); ?></li>
        <li><?php printf(esc_html__('%1$sOpen a topic on the WordPress.org plugin support forums%2$s', 'koko-analytics'), '<a href="https://wordpress.org/support/plugin/koko-analytics/">', '</a>'); ?></li>
    </ul>
</div>

<?php
// Fetch 5 most recent posts from www.kokoanalytics.com
$posts = get_transient('koko_analytics_remote_posts');
if (!$posts) {
    $response = wp_remote_get('https://www.kokoanalytics.com/wp-json/wp/v2/posts?per_page=5');
    if (wp_remote_retrieve_response_code($response) == 200) {
        $body = wp_remote_retrieve_body($response);

        // in case response is 200 but can't be decoded as JSON, use an empty array instead
        $posts = json_decode($body) ?? [];
    } else {
        // store empty array to prevent doing an HTTP request on every page load
        // we'll try again in 24 hours
        $posts = [];
    }
    set_transient('koko_analytics_remote_posts', $posts, HOUR_IN_SECONDS * 24);
}

if (count($posts) > 0) { ?>
<div class="mb-5">
    <h2><?php esc_html_e('Koko Analytics news', 'koko-analytics'); ?></h2>
    <ul class="ul-square">
        <?php foreach ($posts as $p) { ?>
            <li><a href="<?= esc_attr($p->link) ?>"><?= esc_html($p->title->rendered) ?></a></li>
        <?php } ?>
    </ul>
</div>
<?php } ?>


<div class="mb-5">
    <h2><?= esc_html__('Debug info', 'koko-analytics') ?></h2>
    <textarea style="font-family: monospace; font-size: 14px;" class="ka-input" rows="8" spellcheck="false" onfocus="this.select()" readonly><?= esc_textarea(implode("\n", $debug_info)) ?></textarea>
</div>
