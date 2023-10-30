<?php defined('ABSPATH') or exit;

/**
* @var int $realtime
* @var array $posts
*/

?>
<style>
    .ka-ul { margin: 0 -12px; }
    .ka-ul li { padding: 4px 12px; }
    .ka-ul li:nth-child(2n+1) { background-color: #f6f7f7; }
</style>
<h3><?php echo esc_html__('Realtime', 'koko-analytics'); ?></h3>
<p>
    <?php echo sprintf(esc_html__('Your site received %s pageviews in the last hour', 'koko-analytics'), '<strong>' . $realtime . '</strong>'); ?>
</p>
<h3 style="margin-top: 2em;">
   <?php echo esc_html__('Showing site visits over last 14 days', 'koko-analytics'); ?>
</h3>

<div style="min-height: 204px;">
    <div id="koko-analytics-dashboard-widget-mount"></div>
</div>

<h3 style="margin-top: 2em;">
    <?php echo esc_html__('Most viewed pages over last 14 days', 'koko-analytics'); ?>
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
<p style="margin-top: 2em;">
    <a href="<?php echo esc_attr(admin_url('index.php?page=koko-analytics')); ?>">
        <?php echo esc_html__('View all statistics', 'koko-analytics'); ?>
    </a>
</p>
