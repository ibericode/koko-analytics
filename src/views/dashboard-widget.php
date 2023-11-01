<?php defined('ABSPATH') or exit;

/**
* @var int $realtime
* @var array $posts
* @var array $referrers
*/

?>
<style>
    .ka-ul { margin: 0 -12px; }
    .ka-ul li { padding: 4px 12px; }
    .ka-ul li:nth-child(2n+1) { background-color: #f6f7f7; }
</style>

<div id="ka-dashboard-widget-realtime">
    <h3><?php echo esc_html__('Realtime', 'koko-analytics'); ?></h3>
    <p>
        <?php echo sprintf(esc_html__('Your site received %s pageviews in the last hour', 'koko-analytics'), '<strong>' . $realtime . '</strong>'); ?>
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

<?php if (count($posts) > 0) { ?>
<div id="ka-dashboard-widget-top-pages">
    <h3 style="margin-top: 2em;">
        <?php echo esc_html__('Most viewed pages today', 'koko-analytics'); ?>
        <span style="float: right;"><?php echo esc_html__('Pageviews', 'koko-analytics'); ?></span>
    </h3>
    <ul class="ka-ul">
        <?php foreach ($posts as $post) { ?>
        <li>
            <a href="<?php echo esc_attr($post->post_permalink); ?>"><?php echo esc_html($post->post_title); ?></a>
            <span style="float: right;"><?php echo esc_html($post->pageviews); ?></span>
        </li>
        <?php } ?>
    </ul>
</div>
<?php } ?>

<?php if (count($referrers) > 0) { ?>
<div id="ka-dashboard-widget-top-referrers">
    <h3 style="margin-top: 2em;">
        <?php echo esc_html__('Top referrers today', 'koko-analytics'); ?>
        <span style="float: right;"><?php echo esc_html__('Pageviews', 'koko-analytics'); ?></span>
    </h3>
    <ul class="ka-ul">
        <?php foreach ($referrers as $referrer) {  ?>
            <li>
                <a href="<?php echo esc_attr($referrer->url); ?>"><?php echo esc_html($referrer->url); ?></a>
                <span style="float: right;"><?php echo esc_html($referrer->pageviews); ?></span>
            </li>
        <?php } ?>
    </ul>
</div>
<?php } ?>

<p style="margin-top: 2em;">
    <a href="<?php echo esc_attr(admin_url('index.php?page=koko-analytics')); ?>">
        <?php echo esc_html__('View all statistics', 'koko-analytics'); ?>
    </a>
</p>
