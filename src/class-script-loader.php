<?php

namespace KokoAnalytics;

use WP_User;

class ScriptLoader
{
    public function init()
    {
        add_action('wp_head', array($this, 'maybe_enqueue_script'));
    }

    public function maybe_enqueue_script() {
        $settings = get_settings();
        $user = wp_get_current_user();

        // bail if user matches one of excluded roles
        if ($user->exists() && $this->user_has_roles($user, $settings['exclude_user_roles'])) {
            return;
        }

        // TODO: Handle "term" requests so we track both terms and post types.
        $post_id = is_singular() ? (int) get_queried_object_id() : 0;
        $use_custom_endpoint = (defined('KOKO_ANALYTICS_USE_CUSTOM_ENDPOINT') && KOKO_ANALYTICS_USE_CUSTOM_ENDPOINT) || file_exists(ABSPATH . '/koko-analytics-collect.php');
        wp_enqueue_script('koko-analytics-script', plugins_url('assets/dist/js/script.js', KOKO_ANALYTICS_PLUGIN_FILE), array(), KOKO_ANALYTICS_VERSION, true);
        wp_localize_script('koko-analytics-script', 'koko_analytics', array(
            'post_id' => $post_id,
            'tracker_url' => $use_custom_endpoint ? home_url('/koko-analytics-collect.php') : admin_url('admin-ajax.php'),
        ));
    }

    public function user_has_roles(WP_User $user, array $roles) {
        foreach ($user->roles as $user_role) {
            if (in_array($user_role, $roles, true)) {
                return true;
            }
        }

        return false;
    }
}