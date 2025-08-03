<?php

/**
 * Plugin Name: Koko Analytics: Remove pageviews component from Admin Bar
 */

add_action('plugins_loaded', function () {
    remove_action('admin_bar_menu', [\KokoAnalytics\Pro\Admin_Bar::class, 'register'], 120);
});
