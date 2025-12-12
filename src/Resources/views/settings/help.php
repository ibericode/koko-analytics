
<h2 class="mt-0 mb-3"><?= esc_html__('Help', 'koko-analytics') ?></h2>
 <div class="mb-5">
    <ul class="ul-square">
        <li><?php printf(esc_html__('Have a look at our %1$sknowledge base%2$s for help with configuring and using Koko Analytics.', 'koko-analytics'), '<a href="https://www.kokoanalytics.com/kb/" target="_blank">', '</a>'); ?></li>
        <li><?php printf(esc_html__('Go through our %1$srepository of sample code snippets%2$s for inspiration on modifying the default Koko Analytics behavior.', 'koko-analytics'), '<a href="https://github.com/ibericode/koko-analytics/tree/main/code-snippets" target="_blank">', '</a>'); ?></li>
        <li><?php printf(esc_html__('Vote on %1$snew features you would like to have in Koko Analytics%2$s.', 'koko-analytics'), '<a href="https://github.com/ibericode/koko-analytics/discussions?discussions_q=is%3Aopen+sort%3Atop" target="_blank">', '</a>'); ?></li>
    </ul>
</div>

<?php
// Fetch 5 most recent posts from www.kokoanalytics.com
$posts = get_transient('koko_analytics_remote_posts');
if (!$posts) {
    $response = wp_remote_get('https://www.kokoanalytics.com/wp-json/wp/v2/posts?per_page=5');
    if ($response && wp_remote_retrieve_response_code($response) == 200) {
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
    <textarea style="font-family: monospace; font-size: 14px;" class="ka-input" rows="8" spellcheck="false" onfocus="this.select()" readonly>
PHP: <?= esc_html(PHP_VERSION) ?>

MySQL: <?= esc_html($GLOBALS['wpdb']->db_version()) ?>

Koko Analytics: <?= esc_html(KOKO_ANALYTICS_VERSION) ?>

    Database version: <?= esc_html(get_option('koko_analytics_version', '')) ?>

    Last aggregation: <?= date(DATE_W3C, get_option('koko_analytics_last_aggregation_at', 0)) ?> (<?= (int) (time() - get_option('koko_analytics_last_aggregation_at', 0)) ?> seconds ago)
<?php if (defined('KOKO_ANALYTICS_PRO_VERSION')) : ?>
Koko Analytics Pro: <?= KOKO_ANALYTICS_PRO_VERSION ?>

    Database version: <?= esc_html(get_option('koko_analytics_pro_version', '')) ?>
<?php endif; ?>
    </textarea>
</div>
