<?php
/**
* @var string $tab
*/
?>
<?php if (current_user_can('manage_koko_analytics')) { ?>
<div class="ka-admin-nav">
    <a href="<?php echo admin_url('index.php?page=koko-analytics'); ?>" class="ka-admin-nav--link <?php echo $tab === 'dashboard' ? 'current' : ''; ?>">
        <?php echo __('Stats', 'koko-analytics'); ?>
    </a>
    <span> | </span>
    <a href="<?php echo admin_url('index.php?page=koko-analytics&tab=settings'); ?>" class="ka-admin-nav--link last <?php echo $tab === 'settings' ? 'current' : ''; ?>">
        <?php echo __('Settings', 'koko-analytics'); ?>
    </a>
</div>
<?php } ?>
