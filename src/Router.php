<?php

namespace KokoAnalytics;

use InvalidArgumentException;

class Router
{
    public static function url(string $name)
    {
        switch ($name) {
            case 'dashboard-embedded':
                return admin_url('index.php?page=koko-analytics');
                break;

            case 'dashboard-standalone':
                if (get_option('permalink_structure', false)) {
                    return home_url('/koko-analytics-dashboard/');
                }

                return add_query_arg(['koko-analytics-dashboard' => null], home_url());
                break;

            default:
                throw new InvalidArgumentException('No such route: ' . $name);
                break;
        }
    }

    public static function is(string $name): bool
    {
        global $pagenow;

        switch ($name) {
            case 'dashboard-embedded':
                return $pagenow === 'index.php' && ($_GET['page'] ?? '') === 'koko-analytics' && ! isset($_GET['tab']);
                break;

            case 'dashboard-standalone':
                return isset($_GET['koko-analytics-dashboard']) || str_contains($_SERVER['REQUEST_URI'] ?? '', '/koko-analytics-dashboard/');
                break;

            default:
                break;
        }

        return false;
    }
}
