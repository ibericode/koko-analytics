<?php
/**
* @var string $tab
*/
?>
<?php if (current_user_can('manage_koko_analytics')) { ?>
<nav class="mb-3 list-inline text-end mt-0" style="margin-left: auto;">
    <?php if (current_user_can('view_koko_analytics')) : ?>
    <ul class="list-inline m-0">
        <li class="list-inline-item m-0 ms-2"><a href="<?php echo esc_attr(add_query_arg(['koko-analytics-dashboard' => 1], home_url())); ?>" <?php echo isset($_GET['koko-analytics-dashboard']) ? 'class="text-black" aria-current="page"' : ''; ?>><?php esc_html_e('Dashboard (full screen)', 'koko-analytics'); ?></a></li>

        <li class="list-inline-item m-0 ms-2"><a href="<?php echo admin_url('index.php?page=koko-analytics'); ?>" <?php echo $tab === 'dashboard' && !isset($_GET['koko-analytics-dashboard']) ? 'class="text-black" aria-current="page"' : ''; ?>><?php esc_html_e('Dashboard', 'koko-analytics'); ?></a></li>
    <?php endif; ?>
        <?php if (current_user_can('manage_koko_analytics')) : ?>
        <li class="list-inline-item m-0 ms-2"><a href="<?php echo admin_url('index.php?page=koko-analytics&tab=settings'); ?>" <?php echo $tab === 'settings' ? 'class="text-black" aria-current="page"' : ''; ?>><?php esc_html_e('Settings', 'koko-analytics'); ?></a></li>
        <?php endif; ?>
    </ul>
</nav>
<?php } ?>
