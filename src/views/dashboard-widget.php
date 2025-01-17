<?php defined('ABSPATH') or exit;

/**
* @var int $number_of_top_items
* @var int $realtime
* @var array $chart_data
* @var array $posts
* @var array $referrers
* @var stdClass $totals
* @var \DateTimeInterface $dateStart
* @var \DateTimeInterface $dateEnd
*/

use KokoAnalytics\Chart_View;

?>
<link rel="stylesheet" href="<?php echo plugins_url('assets/dist/css/dashboard.css', KOKO_ANALYTICS_PLUGIN_FILE); ?>?v=<?php echo KOKO_ANALYTICS_VERSION; ?>">
<script src="<?php echo plugins_url('assets/dist/js/dashboard-widget.js', KOKO_ANALYTICS_PLUGIN_FILE); ?>?v=<?php echo KOKO_ANALYTICS_VERSION; ?>" defer></script>

<div id="ka-dashboard-widget-realtime">
    <h3><?php esc_html_e('Realtime', 'koko-analytics'); ?></h3>
    <p>
        <?php printf(esc_html__('Your site had a total of %1$s pageviews today, %2$s of which were in the last hour.', 'koko-analytics'), '<strong>' . number_format_i18n($totals->pageviews) . '</strong>', '<strong>' . number_format_i18n($realtime) . '</strong>'); ?>
    </p>
</div>

<div id="ka-dashboard-widget-chart" style="margin-top: 2em;">
    <h3>
       <?php esc_html_e('Showing site visits over last 14 days', 'koko-analytics'); ?>
    </h3>
    <?php new Chart_View($chart_data, $dateStart, $dateEnd, 200); ?>
</div>

<?php if ($number_of_top_items > 0 && (count($posts) > 0 || count($referrers) > 0)) { ?>
<div id="ka-dashboard-widget-top-pages">
    <div style="display: flex; flex-flow: row wrap; margin-top: 2em;">
        <?php if (count($posts) > 0) { ?>
        <div style="width: 50%; box-sizing: border-box; padding-right: 2em;">
            <h3>
                <?php esc_html_e('Today\'s top pages', 'koko-analytics'); ?>
            </h3>
            <ul class="ka-ul">
                <?php foreach ($posts as $post) { ?>
                <li>
                    <span><?php echo number_format_i18n($post->pageviews); ?></span> <a href="<?php echo esc_attr($post->post_permalink); ?>"><?php echo esc_html($post->post_title); ?></a>
                </li>
                <?php } ?>
            </ul>
        </div>
        <?php } // end if count posts ?>
        <?php if (count($referrers) > 0) { ?>
        <div>
            <h3>
            <?php esc_html_e('Today\'s top referrers', 'koko-analytics'); ?>
            </h3>
            <ul class="ka-ul">
                <?php foreach ($referrers as $referrer) {  ?>
                    <li>
                        <span><?php echo number_format_i18n($referrer->pageviews); ?></span> <a href="<?php echo esc_attr($referrer->url); ?>"><?php echo esc_html(parse_url($referrer->url, PHP_URL_HOST)); ?></a>
                    </li>
                <?php } ?>
            </ul>
        </div>
        <?php } ?>
    </div>
</div>
<?php } ?>

<p>
    <a href="<?php echo esc_attr(admin_url('index.php?page=koko-analytics')); ?>">
        <?php esc_html_e('View all statistics', 'koko-analytics'); ?>
    </a>
</p>
