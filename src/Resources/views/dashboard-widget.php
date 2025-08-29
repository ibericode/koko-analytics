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
use KokoAnalytics\Fmt;

?>
<link rel="stylesheet" href="<?php echo plugins_url('assets/dist/css/dashboard-2.css', KOKO_ANALYTICS_PLUGIN_FILE); ?>?v=<?php echo KOKO_ANALYTICS_VERSION; ?>">
<script src="<?php echo plugins_url('assets/dist/js/dashboard-widget.js', KOKO_ANALYTICS_PLUGIN_FILE); ?>?v=<?php echo KOKO_ANALYTICS_VERSION; ?>" defer></script>

<div >
    <div id="ka-dashboard-widget-realtime" class="mb-4">
        <h3 class="mb-2"><?php esc_html_e('Realtime', 'koko-analytics'); ?></h3>
        <p class="m-0">
            <?php printf(esc_html__('Your site had a total of %1$s pageviews today, %2$s of which were in the last hour.', 'koko-analytics'), '<strong>' . number_format_i18n($totals->pageviews) . '</strong>', '<strong>' . number_format_i18n($realtime) . '</strong>'); ?>
        </p>
    </div>

    <div id="ka-dashboard-widget-chart" class="mb-4">
        <h3 class="mb-3">
           <?php esc_html_e('Showing site visits over last 14 days', 'koko-analytics'); ?>
        </h3>
        <div class="">
        <?php new Chart_View($chart_data, $dateStart, $dateEnd, 200, false); ?>
        </div>
    </div>

    <?php if ($number_of_top_items > 0 && (count($posts) > 0 || count($referrers) > 0)) { ?>
    <div id="ka-dashboard-widget-top-pages" class="mb-4">
        <div class="ka-row ka-row-cols-2">
            <?php if (count($posts) > 0) { ?>
            <div class="ka-col">
                <h3 class="mb-2">
                    <?php esc_html_e('Today\'s top pages', 'koko-analytics'); ?>
                </h3>
                <ul class="list-unstyled m-0">
                    <?php foreach ($posts as $post) { ?>
                    <li class="text-truncate">
                        <span class="text-muted me-2"><?php echo number_format_i18n($post->pageviews); ?></span>
                        <a href="<?php echo esc_attr(home_url($post->path)); ?>"><?php echo esc_html($post->label); ?></a>
                    </li>
                    <?php } ?>
                </ul>
            </div>
            <?php } // end if count posts ?>
            <?php if (count($referrers) > 0) { ?>
            <div class="ka-col">
                <h3 class="mb-2">
                <?php esc_html_e('Today\'s top referrers', 'koko-analytics'); ?>
                </h3>
                <ul class="list-unstyled m-0">
                    <?php foreach ($referrers as $referrer) {  ?>
                        <li class="text-truncate">
                            <span class="text-muted me-2"><?php echo number_format_i18n($referrer->pageviews); ?></span>
                            <?php echo Fmt::referrer_url_label(esc_html($referrer->url)); ?>
                        </li>
                    <?php } ?>
                </ul>
            </div>
            <?php } ?>
        </div>
    </div>
    <?php } ?>

    <p class="m-0">
        <a href="<?php echo esc_attr(admin_url('index.php?page=koko-analytics')); ?>">
            <?php esc_html_e('View all statistics', 'koko-analytics'); ?>
        </a>
    </p>
</div>
