<?php

// phpcs:disable PSR1.Files.SideEffects

require dirname(__DIR__) . '/koko-analytics.php';

function apply_filters($a, $b, $prio = 10, $args = 2)
{
    return $b;
}
function add_action($a, $b, $c = 10, $d = 1)
{
}
function add_filter($a, $b, $c = 10, $d = 1)
{
}
function add_shortcode($a, $b)
{
}
function number_format_i18n($number, $decimals = 0)
{
    return number_format($number, $decimals);
}
function register_activation_hook($a, $b)
{
}
function update_option($a, $b, $c = false)
{
}
function get_option($a, $b = null)
{
    return $b;
}
