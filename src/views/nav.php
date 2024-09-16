<?php
/**
* @var string $tab
*/
?>
<?php if (current_user_can('manage_koko_analytics')) { ?>
<div class="ka-admin-nav">
    <?php if ($tab === 'dashboard') { ?>
        <?php if (isset($_GET['koko-analytics-dashboard'])) { ?>
            <a href="<?php echo admin_url('index.php?page=koko-analytics'); ?>" class="ka-admin-nav--link">
                <?php echo esc_html__('Switch to admin view', 'koko-analytics'); ?>
            </a>
        <?php } else { ?>
            <a href="<?php echo esc_attr(add_query_arg(['koko-analytics-dashboard' => 1], home_url())); ?>" class="ka-admin-nav--link">
                <?php echo esc_html__('Switch to standalone view', 'koko-analytics'); ?>
            </a>
        <?php } ?>
    <?php } else { ?>
        <a href="<?php echo admin_url('index.php?page=koko-analytics'); ?>" class="ka-admin-nav--link">
            <?php echo esc_html__('Stats', 'koko-analytics'); ?>
        </a>
    <?php } ?>

    <a href="<?php echo admin_url('index.php?page=koko-analytics&tab=settings'); ?>" class="ka-admin-nav--link last <?php echo $tab === 'settings' ? 'current' : ''; ?>">
        <?php echo esc_html__('Settings', 'koko-analytics'); ?>
    </a>
</div>
<?php } ?>
