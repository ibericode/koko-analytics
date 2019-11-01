<?php

namespace KokoAnalytics;

class Plugin
{
    public function init()
    {
        add_filter('pre_update_option_active_plugins', array($this, 'filter_active_plugins'));
        register_activation_hook(KOKO_ANALYTICS_PLUGIN_FILE, array($this, 'on_activation'));
    }

    public function filter_active_plugins($plugins)
    {
        if (empty( $plugins)) {
            return $plugins;
        }

        $pattern = '/' . preg_quote(plugin_basename(KOKO_ANALYTICS_PLUGIN_FILE), '/') . '$/';
        return array_merge(
            preg_grep($pattern, $plugins),
            preg_grep($pattern, $plugins, PREG_GREP_INVERT)
        );
    }

    public function on_activation()
    {
        update_option('activate_plugins', get_option('active_plugins'));
    }

//
//    public function create_symlink()
//    {
//        if (!file_exists( ABSPATH . '/koko-analytics-collect.php') && function_exists('symlink')) {
//            @symlink( KOKO_ANALYTICS_PLUGIN_DIR . '/collect.php', ABSPATH . '/koko-analytics-collect.php'  );
//        }
//    }
//
//    public function remove_symlink()
//    {
//        if (file_exists( ABSPATH . '/koko-analytics-collect.php' )) {
//            unlink(ABSPATH . '/koko-analytics-collect.php');
//        }
//    }
}