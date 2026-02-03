<?php defined('ABSPATH') or exit; ?>
<?php if (current_user_can('manage_koko_analytics')) { ?>
    <nav class="mb-3 list-inline text-end mt-0" style="margin-left: auto;">
        <ul class="list-inline m-0">
            <li class="list-inline-item m-0 ms-2"><a href="<?= admin_url('options-general.php?page=koko-analytics-settings') ?>"><?php esc_html_e('Settings', 'koko-analytics'); ?></a></li>
        </ul>
    </nav>
<?php } ?>
