<?php

/*
 * phpcs:disable PSR1.Files.SideEffects
 * phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
*/

define('ABSPATH', dirname(__DIR__, 1));
define('HOUR_IN_SECONDS', 3600);

$options = [];
$hooks = [];

function is_admin()
{
    return false;
}

function add_action($hook, $callback, $c = 10, $d = 1)
{
    global $hooks;
    $hooks[$hook] ??= [];
    $hooks[$hook][] = $callback;
}

function do_action($hook, ...$args)
{
    global $hooks;
    $actions = $hooks[$hook] ?? [];
    foreach ($actions as $a) {
        $a();
    }
}

function add_filter($hook, $callback, $c = 10, $d = 1)
{
    add_action($hook, $callback, $c, $d);
}

function apply_filters($hook, $value, $prio = 10, $args = 2)
{
    global $hooks;

    $filters = $hooks[$hook] ?? [];
    foreach ($filters as $cb) {
        $value = $cb($value);
    }

    return $value;
}

function add_shortcode($a, $b) {}

function number_format_i18n($number, $decimals = 0)
{
    return number_format($number, $decimals);
}

function register_activation_hook($file, $callback) {}

function register_deactivation_hook($file, $callback) {}

function update_option($option_name, $value, $autoload = false)
{
    global $options;
    $options[$option_name] = $value;
}

function get_option($option_name, $default = null)
{
    global $options;
    return $options[$option_name] ?? $default;
}

function get_transient($name)
{
    return null;
}

function set_transient($name, $value, $ttl) {}

function delete_transient($name) {}

function get_role($role)
{
    return null;
}

function get_site_url()
{
    return '';
}

function site_url()
{
    return '';
}

function home_url($path = '')
{
    return '';
}

function is_multisite()
{
    return false;
}

function wp_next_scheduled($event)
{
    return false;
}

function wp_schedule_event($timestamp, $recurrence, $hook, $args = []) {}

function wp_upload_dir()
{
    return [
        'basedir' => '/tmp',
    ];
}

function wp_remote_get($url)
{
    return null;
}

function wp_remote_retrieve_response_code($response)
{
    return '';
}

function wp_remote_retrieve_headers($response)
{
    return [];
}

function is_wp_error($thing)
{
    return false;
}

function _deprecated_function($function, $version, $replacement) {}

function wp_timezone(): DateTimeZone
{
    return new DateTimeZone('UTC');
}

function wp_register_script($handle, $src, $deps = []) {}
function plugins_url($file, $path) {}
function register_block_type($file, $args = []) {}

class wpdb_mock
{
    public $prefix = '';
    public function query($sql)
    {
        return null;
    }

    public function prepare($query, ...$params)
    {
        return $query;
    }
}

global $wpdb;
$wpdb = new wpdb_mock();
