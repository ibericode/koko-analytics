<?php

/**
* @var string $tab
*/

use KokoAnalytics\Router;

?>
<?php if (current_user_can('manage_koko_analytics')) { ?>
<nav class="mb-3 list-inline text-end mt-0" style="margin-left: auto;">
    <?php if (current_user_can('view_koko_analytics')) : ?>
    <ul class="list-inline m-0">
        <li class="list-inline-item m-0 ms-2"><a href="<?= Router::url('dashboard-standalone'); ?>" <?= Router::is('dashboard-standalone') ? 'class="text-black" aria-current="page"' : ''; ?>><?php esc_html_e('Dashboard (full screen)', 'koko-analytics'); ?></a></li>

        <li class="list-inline-item m-0 ms-2"><a href="<?= Router::url('dashboard-embedded') ?>" <?= Router::is('dashboard-embedded') ? 'class="text-black" aria-current="page"' : ''; ?>><?php esc_html_e('Dashboard', 'koko-analytics'); ?></a></li>
    <?php endif; ?>
        <?php if (current_user_can('manage_koko_analytics')) : ?>
        <li class="list-inline-item m-0 ms-2"><a href="<?= Router::url('settings-page') ?>" <?= Router::is('settings-page') ? 'class="text-black" aria-current="page"' : ''; ?>><?php esc_html_e('Settings', 'koko-analytics'); ?></a></li>
        <?php endif; ?>
    </ul>
</nav>
<?php } ?>
