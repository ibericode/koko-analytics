<?php
/**
* @var string $tab
*/
?>
<?php if (current_user_can('manage_koko_analytics')) { ?>
<div class="ka-admin-nav">

    <?php if ($tab === 'dashboard') { ?>
        <?php if (isset($_GET['standalone'])) { ?>
            <a href="<?php echo admin_url('index.php?page=koko-analytics'); ?>" class="ka-admin-nav--link">
                <?php echo __('Switch to default view', 'koko-analytics'); ?>
            </a>
        <?php } else { ?>
            <a href="<?php echo admin_url('index.php?page=koko-analytics&standalone'); ?>" class="ka-admin-nav--link">
                <?php echo __('Switch to standalone view', 'koko-analytics'); ?>
            </a>
        <?php } ?>
    <?php } else { ?>
        <a href="<?php echo admin_url('index.php?page=koko-analytics'); ?>" class="ka-admin-nav--link <?php echo $tab === 'dashboard' && ! isset($_GET['standalone']) ? 'current' : ''; ?>">
            <?php echo __('Stats', 'koko-analytics'); ?>
        </a>
    <?php } ?>

    <a href="<?php echo admin_url('index.php?page=koko-analytics&tab=settings'); ?>" class="ka-admin-nav--link last <?php echo $tab === 'settings' ? 'current' : ''; ?>">
        <?php echo __('Settings', 'koko-analytics'); ?>
    </a>
</div>
<?php } ?>
