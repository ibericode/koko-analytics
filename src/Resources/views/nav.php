<?php
/**
* @var string $tab
*/
?>
<?php if (current_user_can('manage_koko_analytics')) { ?>
<nav class="ka-admin-nav">
    <ul>
    <?php if ($tab === 'dashboard') { ?>
        <?php if (isset($_GET['koko-analytics-dashboard'])) { ?>
            <li><a href="<?php echo admin_url('index.php?page=koko-analytics'); ?>">
                <?php esc_html_e('Admin view', 'koko-analytics'); ?>
            </a></li>
        <?php } else { ?>
            <li><a href="<?php echo esc_attr(add_query_arg(['koko-analytics-dashboard' => 1], home_url())); ?>"">
                <?php esc_html_e('Standalone view', 'koko-analytics'); ?>
            </a></li>
        <?php } ?>
    <?php } else { ?>
        <li><a href="<?php echo admin_url('index.php?page=koko-analytics'); ?>">
            <?php esc_html_e('Stats', 'koko-analytics'); ?>
        </a></li>
    <?php } ?>

    <li><a href="<?php echo admin_url('index.php?page=koko-analytics&tab=settings'); ?>" class="<?php echo $tab === 'settings' ? 'current' : ''; ?>">
        <?php esc_html_e('Settings', 'koko-analytics'); ?>
    </a></li>
    <?php if (!defined('KOKO_ANALYTICS_PRO_VERSION')) { ?>
    <li><a href="https://www.kokoanalytics.com/pricing/" style="color:#b60205;"><?php esc_html_e('Upgrade to Pro', 'koko-analytics'); ?></a></li>
    <?php } ?>
</nav>
<?php } ?>
