<?php defined('ABSPATH') or exit;

/**
* @var int $number_of_top_items
* @var int $realtime
* @var array $posts
* @var array $referrers
* @var stdClass $totals
*/

?>


<div id="ka-dashboard-widget-realtime">
    <h3><?php echo esc_html__('Realtime', 'koko-analytics'); ?></h3>
    <p>
        <?php echo sprintf(esc_html__('Your site had a total of %s pageviews today, %s of which  were in the last hour.', 'koko-analytics'), '<strong>' . $totals->pageviews . '</strong>', '<strong>' . $realtime . '</strong>'); ?>
    </p>
</div>

<div id="ka-dashboard-widget-chart" style="display: none;">
    <h3 style="margin-top: 2em;">
       <?php echo esc_html__('Showing site visits over last 14 days', 'koko-analytics'); ?>
    </h3>
    <div id="koko-analytics-dashboard-widget-mount">
        Please wait, your chart is loading. <br />
        If nothing shows up, check your browser console for any error messages.
    </div>
</div>

<style>
    .ka-ul li {
        text-overflow: ellipsis;
        overflow: hidden;
        white-space: nowrap;
    }
    .ka-ul span {
        min-width: 24px;
        display: inline-block;
    }
</style>

<?php if ($number_of_top_items > 0) { ?>
<div id="ka-dashboard-widget-top-pages">
    <div style="display: flex; flex-flow: row wrap; margin-top: 2em;">
        <div style="width: 50%; box-sizing: border-box; padding-right: 2em;">
            <h3>
                <?php echo esc_html__('Today\'s top pages', 'koko-analytics'); ?>
            </h3>
            <ul class="ka-ul">
                <?php foreach ($posts as $post) { ?>
                <li>
                    <span><?php echo $post->pageviews; ?></span> <a href="<?php echo esc_attr($post->post_permalink); ?>"><?php echo esc_html($post->post_title); ?></a>
                </li>
                <?php } ?>
            </ul>
        </div>
        <div style="">
            <h3>
            <?php echo esc_html__('Today\'s top referrers', 'koko-analytics'); ?>
            </h3>
            <ul class="ka-ul">
                <?php foreach ($referrers as $referrer) {  ?>
                    <li>
                        <span><?php echo $referrer->pageviews; ?></span> <a href="<?php echo esc_attr($referrer->url); ?>"><?php echo esc_html(parse_url($referrer->url, PHP_URL_HOST)); ?></a>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
</div>
<?php } ?>

<p style="margin-top: 2em;">
    <a href="<?php echo esc_attr(admin_url('index.php?page=koko-analytics')); ?>">
        <?php echo esc_html__('View all statistics', 'koko-analytics'); ?>
    </a>
</p>
