<?php

namespace KokoAnalytics;

class Dashboard_Standalone extends Dashboard
{
    protected function get_base_url()
    {
        if (get_option('permalink_structure', false)) {
            return home_url('/koko-analytics-dashboard/');
        }

        return add_query_arg(['koko-analytics-dashboard' => null], home_url());
    }

    public function show()
    {
        require KOKO_ANALYTICS_PLUGIN_DIR . '/src/Resources/views/standalone.php';
    }
}
