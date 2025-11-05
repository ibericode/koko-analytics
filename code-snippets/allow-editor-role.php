<?php

add_action('admin_init', function () {
    // add "view_koko_analytics" capability to "editor" role
    $role = get_role('editor');
    if (!$role->has_cap('view_koko_analytics')) {
        $role->add_cap('view_koko_analytics');
    }
});
